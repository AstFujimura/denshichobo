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
            $groups = Group::where('id', '>', 100000)->get();
        } else {
            $groups = DB::table('groups')
            ->leftJoin('group_user', 'groups.id', '=', 'group_user.グループID')
            ->select('groups.*', 'group_user.ユーザーID')
            ->where('group_user.ユーザーID', Auth::user()->id)
            ->where('groups.id', '>', 100000)
            ->get();
        }
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

        $users = DB::table('users')
            ->leftJoin('group_user', 'users.id', '=', 'group_user.ユーザーID')
            ->select('users.*', 'group_user.グループID')
            ->where('group_user.グループID', $selected_group_id)
            ->get();
        foreach ($users as $user) {
            for ($i = 0; $i < 7; $i++) {
                $user->{'index' . $i} = DB::table('events')
                    ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                    ->select('events.*', 'events.id as event_id', 'event_user.ユーザーID')
                    ->where('開始', '>=', $cells->{$i}->ymd)
                    ->where('開始', '<', Carbon::parse($cells->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                    ->where('event_user.ユーザーID', $user->id)
                    ->orderBy('開始', 'asc')
                    ->get();
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
        return view('schedule.schedulemonth', compact("prefix"));
    }
    public function scheduleregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $user_id = $request->user_id;
        $date = $request->date;
        $start_time_hour = "-";
        $start_time_minute = "-";
        $end_time_hour = "-";
        $end_time_minute = "-";
        $event_name = "";
        $event_id = $request->event_id ?? null;
        $event = Event::find($event_id);
        if ($event) {
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
        }
        return view('schedule.scheduleregist', compact("prefix", "server", "user_id", "date", "start_time_hour", "start_time_minute", "end_time_hour", "end_time_minute", "event_name", "event_id"));
    }
    public function scheduleregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $event_id = $request->event_id;
        $event = Event::find($event_id);
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
        $event->予定詳細 = $request->event_name ?? "--";
        $event->save();

        Event_User::where('イベントID', $event_id)->delete();
        // 後々複数のユーザーを登録
        $event_user = new Event_User();
        $event_user->イベントID = $event->id;
        $event_user->ユーザーID = Auth::user()->id;
        $event_user->save();
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
}
