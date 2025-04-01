<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Event;
use App\Models\Regular_event;
use App\Models\Plan;
use App\Models\Event_User;
use App\Models\Regular_Event_User;
use App\Models\Facility;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\Schedule_group;
use App\Models\Schedule_Group_User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use stdClass;
use TCPDF;
use setasign\Fpdi\TcpdfFpdi;
use \TCPDF_FONTS;

use Carbon\Carbon;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Tcpdf\Fpdi;

class ScheduleController extends Controller
{
    public function scheduleget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == '管理') {
            $groups = DB::table('groups')
                ->where('id', '>', 100000)
                ->get();
        } else {
            $groups = DB::table('groups')
                ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
                ->select('groups.*', 'group_user.ユーザーID')
                ->where('group_user.ユーザーID', Auth::user()->id)
                ->where('groups.id', '>', 100000)
                ->get();
        }
        $schedule_groups = DB::table('schedule_groups')
            ->leftJoin('schedule_group_user', 'schedule_groups.id', '=', 'schedule_group_user.個人グループID')
            ->select('schedule_groups.*', 'schedule_group_user.ユーザーID')
            ->where('schedule_groups.登録ユーザーID', Auth::user()->id)
            ->get()
            ->unique('id')
            ->sortByDesc(function ($schedule_group) {
                return $schedule_group->デフォルト ? 1 : 0;
            })->values(); // ソート後のインデックスを振り直す;
        foreach ($schedule_groups as $schedule_group) {
            $schedule_group->グループ名 = $schedule_group->グループ名 . " (個人グループ)";
        }

        $groups = $schedule_groups->merge($groups);
        // dd($groups);
        $selected_group_id = $request->selected_group_id ?? $groups->first()->id;
        $base_date = $request->base_date ?? Carbon::now()->format('Y-m-d');
        $base_day = Carbon::parse($base_date)->format('w');
        $week_array = ['日', '月', '火', '水', '木', '金', '土'];
        $cells = new \stdClass();
        for ($i = 0; $i < 7; $i++) {
            $cells->{$i} = new \stdClass();
            $cells->{$i}->day = $week_array[($base_day + $i) % 7];
            $cells->{$i}->date = Carbon::parse($base_date)->addDays($i)->format('d');
            $cells->{$i}->day_num = Carbon::parse($base_date)->addDays($i)->format('w');
            $cells->{$i}->ymd = Carbon::parse($base_date)->addDays($i)->format('Y-m-d');
        }

        if ($selected_group_id > 100000) {
            $users = DB::table('users')
                ->leftJoin('group_user', 'users.id', '=', 'group_user.ユーザーID')
                ->select('users.*', 'group_user.グループID')
                ->where('group_user.グループID', $selected_group_id)
                ->get()
                ->sortByDesc(function ($user) {
                    return $user->id === Auth::user()->id ? 1 : 0;
                })->values(); // ソート後のインデックスを振り直す
        } else {
            $users = DB::table('users')
                ->leftJoin('schedule_group_user', 'users.id', '=', 'schedule_group_user.ユーザーID')
                ->select('users.*', 'schedule_group_user.個人グループID')
                ->where('schedule_group_user.個人グループID', $selected_group_id)
                ->get()
                ->sortByDesc(function ($user) {
                    return $user->id === Auth::user()->id ? 1 : 0;
                })->values(); // ソート後のインデックスを振り直す
        }
        foreach ($users as $user) {
            for ($i = 0; $i < 7; $i++) {
                $user->{'index' . $i} = DB::table('events')
                    ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                    ->leftJoin('plans', 'events.予定ID', '=', 'plans.id')
                    ->select('events.*', 'events.id as event_id', 'event_user.ユーザーID', 'plans.予定', 'plans.装飾')
                    ->where('開始', '>=', $cells->{$i}->ymd)
                    ->where('開始', '<', Carbon::parse($cells->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                    ->where('終了', '>=', $cells->{$i}->ymd)
                    ->where('終了', '<', Carbon::parse($cells->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                    ->where('event_user.ユーザーID', $user->id)
                    ->orderBy('開始', 'asc')
                    ->get();
            }
            $user->long_events = DB::table('events')
                ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                ->leftJoin('plans', 'events.予定ID', '=', 'plans.id')
                ->select('events.*', 'events.id as event_id', 'event_user.ユーザーID', 'plans.予定', 'plans.装飾')
                ->where('event_user.ユーザーID', $user->id)
                ->where(function ($query) use ($cells) {
                    $startOfWeek = Carbon::parse($cells->{0}->ymd);
                    $endOfWeek = Carbon::parse($cells->{6}->ymd)->addDays(1)->format('Y-m-d'); // 週の終わり+1日
                    $query->where('開始', '<', $endOfWeek)  // 週の最後の日より前に始まる
                        ->where('終了', '>=', $startOfWeek); // 週の最初の日以降に終わる
                })
                ->whereRaw('DATE(開始) != DATE(終了)') // 2日以上続くイベントのみ
                ->orderBy('開始', 'asc')
                ->get();

            $long_events_array = ['1' => -1];
            foreach ($user->long_events as $long_event) {
                if (Carbon::parse($long_event->開始) < Carbon::parse($base_date)) {
                    $start_day_num = 0;
                } else {
                    $start_day_num = Carbon::parse($long_event->開始)->diffInDays(Carbon::parse($base_date));
                }
                $end_day_num = Carbon::parse($long_event->終了)->diffInDays(Carbon::parse($base_date));
                $assigned = false;

                foreach ($long_events_array as $key => $value) {
                    if ($value < $start_day_num) { // 既存のキーのバリューが start_day_num にかぶらなければ更新
                        $long_events_array[$key] = $end_day_num;
                        $assigned = true;
                        $long_event->start_row = $key;
                        $long_event->end_row = $key + 1;
                        $long_event->start_col = $start_day_num + 2;
                        $long_event->end_col = $end_day_num + 3;
                        if ($long_event->end_col > 9) {
                            $long_event->end_col = 9;
                        }
                        break;
                    }
                }

                if (!$assigned) { // すべてのキーがかぶっていた場合、新しいキーを追加
                    $new_key = max(array_keys($long_events_array)) + 1;
                    $long_events_array[$new_key] = $end_day_num;
                    $long_event->start_row = $new_key;
                    $long_event->end_row = $new_key + 1;
                    $long_event->start_col = $start_day_num + 2;
                    $long_event->end_col = $end_day_num + 3;
                    if ($long_event->end_col > 9) {
                        $long_event->end_col = 9;
                    }
                }
            }
        }
        return view('schedule.schedule', compact("prefix", "server", "groups", "base_date", "selected_group_id", "week_array", "cells", "users"));
    }
    public function scheduleweekget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        return view('schedule.scheduleweek', compact("prefix"));
    }
    public function schedulemonthget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        

        $user = DB::table('users')
            ->find($request->user_id ?? Auth::user()->id);
        if (Auth::user()->管理 == '管理') {
            $selected_users = User::where('id', '>', 1)
            ->get()
            ->sortByDesc(function ($user) {
                return $user->id === Auth::user()->id ? 1 : 0;
            })->values(); // ソート後のインデックスを振り直す
        } else {
            $selected_users = DB::table('users')
                ->leftJoin('group_user', 'users.id', '=', 'group_user.ユーザーID')
                ->select('users.*', 'group_user.グループID')
                ->where('group_user.グループID', Auth::user()->id)
                ->get()
                ->sortByDesc(function ($user) {
                    return $user->id === Auth::user()->id ? 1 : 0;
                })->values(); // ソート後のインデックスを振り直す
        }

        $month = $request->month ?? Carbon::now()->format('Y-m');
        $first_day = Carbon::parse($month)->startOfMonth()->format('w');
        $base_date = Carbon::parse($month)->startOfMonth()->subDays($first_day)->format('Y-m-d');
        // $base_day = Carbon::parse($base_date)->format('w');
        $week_array = ['日', '月', '火', '水', '木', '金', '土'];
        $cells = new \stdClass();
        for ($w = 0; $w < 5; $w++) {
            $cells->{$w} = new \stdClass();
            for ($i = 0; $i < 7; $i++) {
                $add_days = $w * 7 + $i;
                $cells->{$w}->{$i} = new \stdClass();
                $cells->{$w}->{$i}->day = $week_array[$i];
                $cells->{$w}->{$i}->date = Carbon::parse($base_date)->addDays($add_days)->format('d');
                $cells->{$w}->{$i}->day_num = Carbon::parse($base_date)->addDays($add_days)->format('w');
                $cells->{$w}->{$i}->ymd = Carbon::parse($base_date)->addDays($add_days)->format('Y-m-d');
            }
        }
        for ($w = 0; $w < 5; $w++) {
            $user->{$w} = new \stdClass();
            for ($i = 0; $i < 7; $i++) {
                $user->{$w}->{'index' . $i} = DB::table('events')
                    ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                    ->leftJoin('plans', 'events.予定ID', '=', 'plans.id')
                    ->select('events.*', 'events.id as event_id', 'event_user.ユーザーID', 'plans.予定', 'plans.装飾')
                    ->where('開始', '>=', $cells->{$w}->{$i}->ymd)
                    ->where('開始', '<', Carbon::parse($cells->{$w}->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                    ->where('終了', '>=', $cells->{$w}->{$i}->ymd)
                    ->where('終了', '<', Carbon::parse($cells->{$w}->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                    ->where('event_user.ユーザーID', $user->id)
                    ->orderBy('開始', 'asc')
                    ->get();
            }
            $user->{$w}->{'long_events'} = DB::table('events')
                ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                ->leftJoin('plans', 'events.予定ID', '=', 'plans.id')
                ->select('events.*', 'events.id as event_id', 'event_user.ユーザーID', 'plans.予定', 'plans.装飾')
                ->where('event_user.ユーザーID', $user->id)
                ->where(function ($query) use ($cells, $w) {
                    $startOfWeek = Carbon::parse($cells->{$w}->{0}->ymd);
                    $endOfWeek = Carbon::parse($cells->{$w}->{6}->ymd)->addDays(1)->format('Y-m-d'); // 週の終わり+1日
                    $query->where('開始', '<', $endOfWeek)  // 週の最後の日より前に始まる
                        ->where('終了', '>=', $startOfWeek); // 週の最初の日以降に終わる
                })
                ->whereRaw('DATE(開始) != DATE(終了)') // 2日以上続くイベントのみ
                ->orderBy('開始', 'asc')
                ->get();

            $long_events_array = ['1' => -1];
            foreach ($user->{$w}->{'long_events'} as $long_event) {
                if (Carbon::parse($long_event->開始) < Carbon::parse($cells->{$w}->{0}->ymd)) {
                    $start_day_num = 0;
                } else {
                    $start_day_num = Carbon::parse($long_event->開始)->diffInDays(Carbon::parse($cells->{$w}->{0}->ymd));
                }
                $end_day_num = Carbon::parse($long_event->終了)->diffInDays(Carbon::parse($cells->{$w}->{0}->ymd));
                $assigned = false;

                foreach ($long_events_array as $key => $value) {
                    if ($value < $start_day_num) { // 既存のキーのバリューが start_day_num にかぶらなければ更新
                        $long_events_array[$key] = $end_day_num;
                        $assigned = true;
                        $long_event->start_row = $key;
                        $long_event->end_row = $key + 1;
                        $long_event->start_col = $start_day_num + 1;
                        $long_event->end_col = $end_day_num + 2;
                        if ($long_event->end_col > 8) {
                            $long_event->end_col = 8;
                        }
                        break;
                    }
                }

                if (!$assigned) { // すべてのキーがかぶっていた場合、新しいキーを追加
                    $new_key = max(array_keys($long_events_array)) + 1;
                    $long_events_array[$new_key] = $end_day_num;
                    $long_event->start_row = $new_key;
                    $long_event->end_row = $new_key + 1;
                    $long_event->start_col = $start_day_num + 1;
                    $long_event->end_col = $end_day_num + 2;
                    if ($long_event->end_col > 8) {
                        $long_event->end_col = 8;
                    }
                }
            }
        }
        return view('schedule.schedulemonth', compact("prefix", "server","month", "selected_users", "base_date",  "week_array", "cells", "user"));
    }
    public function scheduleregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $user_id = $request->user_id ?? Auth::user()->id;
        $date = $request->date;
        $start_time_hour = "-";
        $start_time_minute = "-";
        $end_time_hour = "-";
        $end_time_minute = "-";
        $event_name = "";
        $event_id = $request->event_id ?? null;
        $event = Event::find($event_id);
        $plans = Plan::all();
        $plan_id = "";
        $memo = "";
        $regular_event = "false";
        $long_event = "false";

        if ($event) {
            if ($event->定期イベントID) {
                $regular_event = "true";
                $regular_event_id = $event->定期イベントID;
            }
            if (Carbon::parse($event->開始)->format('Y/m/d') != Carbon::parse($event->終了)->format('Y/m/d')) {
                $long_event = "true";
            }


            $date = Carbon::parse($event->開始)->format('Y/m/d');
            if ($event->開始時間指定) {
                $start_time_hour = Carbon::parse($event->開始)->format('H');
                $start_time_minute = Carbon::parse($event->開始)->format('i');
            } else {
                $start_time_hour = "-";
                $start_time_minute = "-";
            }
            if ($event->終了時間指定) {
                $end_time_hour = Carbon::parse($event->終了)->format('H');
                $end_time_minute = Carbon::parse($event->終了)->format('i');
            } else {
                $end_time_hour = "-";
                $end_time_minute = "-";
            }
            $event_name = $event->予定詳細;
            $plan_id = $event->予定ID;
            $memo = $event->メモ;

            $event_users = DB::table('event_user')
                ->leftJoin('users', 'event_user.ユーザーID', '=', 'users.id')
                ->where('イベントID', $event_id)
                ->select('users.name', 'users.id')
                ->get();
        } else {
            $event_users = User::where('id', $user_id)->get();
        }

        if (Auth::user()->管理 == '管理') {
            $groups = Group::where('id', '>', 100000)->get();
        } else {
            $groups = DB::table('groups')
                ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
                ->select('groups.*', 'group_user.ユーザーID')
                ->where('group_user.ユーザーID', Auth::user()->id)
                ->where('groups.id', '>', 100000)
                ->get();
        }
        if ($regular_event == "true") {
            return redirect()->route('scheduleregularregistget', ['user_id' => $user_id, 'regular_event_id' => $regular_event_id]);
        } else if ($long_event == "true") {
            return redirect()->route('scheduletermregistget', ['user_id' => $user_id, 'event_id' => $event_id]);
        } else {
            return view('schedule.scheduleregist', compact("prefix", "server", "user_id", "date", "start_time_hour", "start_time_minute", "end_time_hour", "end_time_minute", "event_name", "event_users", "event_id", "groups", "plans", "plan_id", "memo"));
        }
    }
    public function scheduleregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $event_id = $request->event_id;
        $event = Event::find($event_id);

        if ($request->delete_flag == "true") {
            $event->delete();
            return redirect()->route('scheduleget');
        }
        if (!$event) {
            $event = new Event();
        }

        if ($request->start_time_hour == "-" || $request->start_time_minute == "-") {
            $event->開始時間指定 = false;
            $event->開始 = $request->date;
        } else {
            $event->開始時間指定 = true;
            $event->開始 = $request->date . " " . $request->start_time_hour . ":" . $request->start_time_minute;
        }
        if ($request->end_time_hour == "-" || $request->end_time_minute == "-") {
            $event->終了時間指定 = false;
            $event->終了 = $request->date;
        } else {
            $event->終了時間指定 = true;
            $event->終了 = $request->date . " " . $request->end_time_hour . ":" . $request->end_time_minute;
        }
        if ($request->schedule_type == "-") {
            $event->予定ID = null;
        } else {
            $event->予定ID = $request->schedule_type;
        }
        $event->予定詳細 = $request->event_name ?? "--";
        $event->メモ = $request->memo;
        $event->save();

        Event_User::where('イベントID', $event_id)->delete();

        foreach ($request->user_id as $user_id) {
            if (User::find($user_id)) {
                $event_user = new Event_User();
                $event_user->イベントID = $event->id;
                $event_user->ユーザーID = $user_id;
                $event_user->save();
            }
        }
        return redirect()->route('scheduleget');
    }
    public function scheduledeletepost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
    }
    public function scheduleholidayget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
    }
    public function scheduleholidayregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
    }
    public function scheduleholidayregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
    }
    // 候補者の取得
    public function schedulecandidateget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $group_id = $request->group_id;
        $users = DB::table('users')
            ->leftJoin('group_user', 'users.id', '=', 'group_user.ユーザーID')
            ->select('users.name', 'users.id')
            ->where('group_user.グループID', $group_id)
            ->get();
        return response()->json($users);
    }
    public function schedulegroupregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $selected_group_id = $request->selected_group_id ?? null;
        if ($selected_group_id) {
            $schedule_group = Schedule_group::find($selected_group_id);
            $default_checkbox = $schedule_group->デフォルト ?? false;
        } else {
            $schedule_group = null;
            $default_checkbox = false;
        }
        $schedule_group_users = DB::table('schedule_group_user')
            ->leftJoin('users', 'schedule_group_user.ユーザーID', '=', 'users.id')
            ->where('個人グループID', $selected_group_id)
            ->select('users.name', 'users.id')
            ->get();

        $server = config('prefix.server');
        if (Auth::user()->管理 == '管理') {
            $groups = Group::where('id', '>', 100000)->get();
        } else {
            $groups = DB::table('groups')
                ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
                ->select('groups.*', 'group_user.ユーザーID')
                ->where('group_user.ユーザーID', Auth::user()->id)
                ->where('groups.id', '>', 100000)
                ->get();
        }
        return view('schedule.schedulegroupregist', compact("prefix", "server", "groups", "schedule_group_users", "schedule_group", "default_checkbox"));
    }
    public function schedulegroupregistpost(Request $request)
    {

        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        // 削除の場合
        if ($request->delete_flag == "true") {
            $schedule_group = Schedule_group::find($request->schedule_group_id);
            $schedule_group->delete();
            return redirect()->route('scheduleget');
        }
        // 変更の場合
        if ($request->schedule_group_id) {
            $schedule_group = Schedule_group::find($request->schedule_group_id);
        }
        // 新規の場合
        else {
            $schedule_group = new Schedule_group();
        }
        if ($request->default_checkbox) {
            $schedule_group->デフォルト = true;
            Schedule_group::where('登録ユーザーID', Auth::user()->id)->update(['デフォルト' => false]);
        } else {
            $schedule_group->デフォルト = false;
        }
        $schedule_group->グループ名 = $request->group_name;
        $schedule_group->登録ユーザーID = Auth::user()->id;
        $schedule_group->save();

        Schedule_Group_User::where('個人グループID', $schedule_group->id)->delete();
        foreach ($request->user_id as $user_id) {
            if (User::find($user_id)) {
                $schedule_group_user = new Schedule_Group_User();
                $schedule_group_user->個人グループID = $schedule_group->id;
                $schedule_group_user->ユーザーID = $user_id;
                $schedule_group_user->save();
            }
        }

        return redirect()->route('scheduleget');
    }
    public function scheduleregularregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $user_id = $request->user_id ?? Auth::user()->id;
        $start_date = $request->start_date ?? Carbon::now()->format('Y/m/d');
        $end_date = $request->end_date ?? Carbon::now()->addMonth(3)->format('Y/m/d');
        $start_time_hour = "-";
        $start_time_minute = "-";
        $end_time_hour = "-";
        $end_time_minute = "-";
        $event_name = "";
        $plan_id = "";
        $regular_event_id = $request->regular_event_id ?? null;
        $regular_event = Regular_event::find($regular_event_id);
        $regular_frequency = "";
        $regular_frequency_day_detail = "";
        $regular_frequency_date_detail = "";
        $plans = Plan::all();
        $memo = "";
        if ($regular_event) {
            $regular_frequency = $regular_event->頻度;
            if ($regular_frequency == 1) {
                $regular_frequency_day_detail = $regular_event->曜日;
            } else if ($regular_frequency == 2) {
                $regular_frequency_date_detail = $regular_event->日付;
            }
            $start_date = Carbon::parse($regular_event->開始期間)->format('Y/m/d');
            $end_date = Carbon::parse($regular_event->終了期間)->format('Y/m/d');
            if ($regular_event->開始時間指定) {
                $start_time_hour = Carbon::parse($regular_event->開始)->format('H');
                $start_time_minute = Carbon::parse($regular_event->開始)->format('i');
            } else {
                $start_time_hour = "-";
                $start_time_minute = "-";
            }
            if ($regular_event->終了時間指定) {
                $end_time_hour = Carbon::parse($regular_event->終了)->format('H');
                $end_time_minute = Carbon::parse($regular_event->終了)->format('i');
            } else {
                $end_time_hour = "-";
                $end_time_minute = "-";
            }
            $event_name = $regular_event->予定詳細;
            $plan_id = $regular_event->予定ID;
            $memo = $regular_event->メモ;
            $event_users = DB::table('regular_event_user')
                ->leftJoin('users', 'regular_event_user.ユーザーID', '=', 'users.id')
                ->where('定期イベントID', $regular_event_id)
                ->select('users.name', 'users.id')
                ->get();
        } else {
            $event_users = User::where('id', $user_id)->get();
        }

        if (Auth::user()->管理 == '管理') {
            $groups = Group::where('id', '>', 100000)->get();
        } else {
            $groups = DB::table('groups')
                ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
                ->select('groups.*', 'group_user.ユーザーID')
                ->where('group_user.ユーザーID', Auth::user()->id)
                ->where('groups.id', '>', 100000)
                ->get();
        }
        return view('schedule.scheduleregularregist', compact("prefix", "server", "user_id", "start_date", "end_date", "start_time_hour", "start_time_minute", "end_time_hour", "end_time_minute", "event_name", "event_users", "regular_event_id", "groups", "regular_frequency", "regular_frequency_day_detail", "regular_frequency_date_detail", "plans", "plan_id", "memo"));
    }
    public function scheduleregularregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        if ($request->regular_event_id) {
            $regular_event = Regular_event::find($request->regular_event_id);
            if ($request->delete_flag == "true") {
                $regular_event->delete();
                return redirect()->route('scheduleget');
            }
        } else {
            $regular_event = new Regular_event();
        }
        $regular_event->頻度 = $request->regular_frequency;
        if ($request->regular_frequency == 1) {
            $regular_event->曜日 = $request->regular_frequency_day_detail;
        } else if ($request->regular_frequency == 2) {
            $regular_event->日付 = $request->regular_frequency_date_detail;
        }
        $regular_event->開始期間 = $request->start_date;
        $regular_event->終了期間 = $request->end_date;
        $regular_event->予定詳細 = $request->event_name ?? "--";
        $regular_event->メモ = $request->memo;

        if ($request->start_time_hour == "-" || $request->start_time_minute == "-") {
            $regular_event->開始時間指定 = false;
        } else {
            $regular_event->開始時間指定 = true;
            $regular_event->開始 = $request->start_time_hour . ":" . $request->start_time_minute;
        }
        if ($request->end_time_hour == "-" || $request->end_time_minute == "-") {
            $regular_event->終了時間指定 = false;
        } else {
            $regular_event->終了時間指定 = true;
            $regular_event->終了 = $request->end_time_hour . ":" . $request->end_time_minute;
        }
        if ($request->schedule_type == "-") {
            $regular_event->予定ID = null;
        } else {
            $regular_event->予定ID = $request->schedule_type;
        }


        $regular_event->save();

        // 定期イベントとユーザーIDの紐づけ
        Regular_Event_User::where('定期イベントID', $regular_event->id)->delete();
        foreach ($request->user_id as $user_id) {
            if (User::find($user_id)) {
                $regular_event_user = new Regular_Event_User();
                $regular_event_user->定期イベントID = $regular_event->id;
                $regular_event_user->ユーザーID = $user_id;
                $regular_event_user->save();
            }
        }

        // イベントの登録処理
        $start_date = Carbon::parse($regular_event->開始期間);
        $end_date = Carbon::parse($regular_event->終了期間);

        // 一旦該当定期イベントIDのイベントを削除
        Event::where('定期イベントID', $regular_event->id)->delete();
        if ($regular_event->頻度 == 0) {
            // 毎日
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                $this->createEvent($regular_event->id, $date, $request->user_id);
            }
        } else if ($regular_event->頻度 == 1) {
            // 毎週
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                if ($date->format('w') === $regular_event->曜日) {
                    $this->createEvent($regular_event->id, $date, $request->user_id);
                }
            }
        } else if ($regular_event->頻度 == 2) {
            // 毎月
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addMonth()) {
                $target_date = Carbon::create($date->year, $date->month, $regular_event->日付);
                if ($target_date->between($start_date, $end_date)) {
                    $this->createEvent($regular_event->id, $target_date, $request->user_id);
                }
            }
        }

        return redirect()->route('scheduleget');
    }
    public function scheduletermregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $user_id = $request->user_id ?? Auth::user()->id;
        $start_date = $request->start_date ?? Carbon::now()->format('Y/m/d');
        $end_date = $request->end_date ?? Carbon::now()->addDay(1)->format('Y/m/d');
        $start_time_hour = "-";
        $start_time_minute = "-";
        $end_time_hour = "-";
        $end_time_minute = "-";
        $event_name = "";
        $plan_id = "";
        $event_id = $request->event_id ?? null;
        $event = Event::find($event_id);
        $plans = Plan::all();
        $memo = "";

        $regular_event = "false";

        if ($event) {
            $start_date = Carbon::parse($event->開始)->format('Y/m/d');
            $end_date = Carbon::parse($event->終了)->format('Y/m/d');
            if ($event->開始時間指定) {
                $start_time_hour = Carbon::parse($event->開始)->format('H');
                $start_time_minute = Carbon::parse($event->開始)->format('i');
            } else {
                $start_time_hour = "-";
                $start_time_minute = "-";
            }
            if ($event->終了時間指定) {
                $end_time_hour = Carbon::parse($event->終了)->format('H');
                $end_time_minute = Carbon::parse($event->終了)->format('i');
            } else {
                $end_time_hour = "-";
                $end_time_minute = "-";
            }
            if ($event->予定ID) {
                $plan_id = $event->予定ID;
            }
            $event_name = $event->予定詳細;
            $memo = $event->メモ;

            $event_users = DB::table('event_user')
                ->leftJoin('users', 'event_user.ユーザーID', '=', 'users.id')
                ->where('イベントID', $event_id)
                ->select('users.name', 'users.id')
                ->get();
        } else {
            $event_users = User::where('id', $user_id)->get();
        }

        if (Auth::user()->管理 == '管理') {
            $groups = Group::where('id', '>', 100000)->get();
        } else {
            $groups = DB::table('groups')
                ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
                ->select('groups.*', 'group_user.ユーザーID')
                ->where('group_user.ユーザーID', Auth::user()->id)
                ->where('groups.id', '>', 100000)
                ->get();
        }
        return view('schedule.scheduletermregist', compact("prefix", "server", "user_id", "start_date", "end_date", "start_time_hour", "start_time_minute", "end_time_hour", "end_time_minute", "event_name", "event_users", "event_id", "groups", "plans", "plan_id", "memo"));
    }
    public function scheduletermregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $event_id = $request->event_id;
        $event = Event::find($event_id);

        if ($request->delete_flag == "true") {
            $event->delete();
            return redirect()->route('scheduleget');
        }
        if (!$event) {
            $event = new Event();
        }

        if ($request->start_time_hour == "-" || $request->start_time_minute == "-") {
            $event->開始時間指定 = false;
            $event->開始 = $request->start_date;
        } else {
            $event->開始時間指定 = true;
            $event->開始 = $request->start_date . " " . $request->start_time_hour . ":" . $request->start_time_minute;
        }
        if ($request->end_time_hour == "-" || $request->end_time_minute == "-") {
            $event->終了時間指定 = false;
            $event->終了 = $request->end_date;
        } else {
            $event->終了時間指定 = true;
            $event->終了 = $request->end_date . " " . $request->end_time_hour . ":" . $request->end_time_minute;
        }
        if ($request->schedule_type == "-") {
            $event->予定ID = null;
        } else {
            $event->予定ID = $request->schedule_type;
        }
        $event->予定詳細 = $request->event_name ?? "--";
        $event->メモ = $request->memo;
        $event->save();

        Event_User::where('イベントID', $event_id)->delete();

        foreach ($request->user_id as $user_id) {
            if (User::find($user_id)) {
                $event_user = new Event_User();
                $event_user->イベントID = $event->id;
                $event_user->ユーザーID = $user_id;
                $event_user->save();
            }
        }
        return redirect()->route('scheduleget');
    }

    public function schedulemasterregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $plans = Plan::all();
        return view('schedule.schedulemaster', compact("prefix", "server", "plans"));
    }

    public function schedulemasterregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $plan_id = $request->plan_id ?? [];
        $delete_plan_id = $request->delete_plan_id ?? [];
        foreach ($plan_id as $plan_id) {
            $plan = Plan::find($plan_id);
            if (!$plan) {
                $plan = new Plan();
            }
            $plan->予定 = $request->input('schedule_name' . $plan_id);
            $plan->装飾 = $request->input('background_color' . $plan_id);
            $plan->save();
        }
        foreach ($delete_plan_id as $delete_plan_id) {
            $plan = Plan::find($delete_plan_id);
            if ($plan) {
                Event::where('予定ID', $plan->id)->update(['予定ID' => null]);
                Regular_event::where('予定ID', $plan->id)->update(['予定ID' => null]);
                $plan->delete();
            }
        }
        return redirect()->route('scheduleget');
    }
    public function schedulecsvget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('schedule.schedulecsv', compact("prefix", "server"));
    }
    public function schedulecsvpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $file = $request->file('csv_file');
        $csv_data = array_map('str_getcsv', file($file));
    }


    /**
     * イベントを作成する
     */
    private function createEvent($regular_event_id, $date, $user_id)
    {
        $regular_event = Regular_event::find($regular_event_id);
        $event = new Event();
        $event->定期イベントID = $regular_event_id;
        $event->開始 = $date->format('Y-m-d') . " " . $regular_event->開始;
        $event->終了 = $date->format('Y-m-d') . " " . $regular_event->終了;
        $event->開始時間指定 = $regular_event->開始時間指定;
        $event->終了時間指定 = $regular_event->終了時間指定;
        $event->予定詳細 = $regular_event->予定詳細;
        $event->予定ID = $regular_event->予定ID;
        $event->save();

        Event_User::where('イベントID', $event->id)->delete();
        foreach ($user_id as $user_id) {
            if (User::find($user_id)) {
                $event_user = new Event_User();
                $event_user->イベントID = $event->id;
                $event_user->ユーザーID = $user_id;
                $event_user->save();
            }
        }
    }
}
