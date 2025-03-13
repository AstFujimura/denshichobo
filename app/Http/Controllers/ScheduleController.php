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
        $users = User::where('id', Auth::user()->id)->get();
        foreach ($users as $user) {
            for($i = 0; $i < 7; $i++) {
                $user->{'index' . $i} = DB::table('events')
                ->leftJoin('event_user', 'events.id', '=', 'event_user.イベントID')
                ->where('開始', '>=', $cells->{$i}->ymd)
                ->where('開始', '<', Carbon::parse($cells->{$i}->ymd)->addDays(1)->format('Y-m-d'))
                ->where('event_user.ユーザーID', $user->id)
                ->get();
            }
        }
        return view('schedule.schedule', compact("prefix", "server", "week_array", "cells", "users"));
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
        return view('schedule.scheduleregist', compact("prefix", "server", "user_id", "date"));
    }
    public function scheduleregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $event = new Event();
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
