@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">
    <div class="loading_container">
        <div class="loading_content">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/loading.gif') }}" alt="">
        </div>
        <div class="loading_text">
            AI解析中です
        </div>
    </div>
    <div class="company_candidate_container_background"></div>
    <div class="company_candidate_container">

    </div>
    <div class="crop_controller_container display_none">
        <div class="crop_controller_content display_none" data-card_type="front">
            <img src="" class="croppable_image" data-card_type="front">
            <div class="crop_complete_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/complete.svg') }}" alt="">
            </div>
            <div class="crop_cancel_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/cancel.svg') }}" alt="">
            </div>
            <!-- <div class="crop_rotate_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/rotate.svg')}}" alt="">
            </div> -->
        </div>
        <div class="crop_controller_content display_none" data-card_type="back">
            <img src="" class="croppable_image" data-card_type="back">
            <div class="crop_complete_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/complete.svg') }}" alt="">
            </div>
            <div class="crop_cancel_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/cancel.svg') }}" alt="">
            </div>
        </div>
    </div>
    <h2 class="pagetitle" id="card_regist_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">
        @if ($edit == 'add')
        {{$carduser->表示名}}さん 名刺追加
        @elseif ($edit == 'edit')
        {{$card->名前}}さん 名刺編集
        @else
        名刺登録
        @endif
    </h2>


    <div class="card_regist_container">
        <form class="card_regist_form" action="{{ route('cardregistpost') }}" id="card_regist_form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="submit_button">登録</div>
            <input type="hidden" name="company_id" id="company_id" value="{{$card->会社ID ?? 0}}">
            <input type="hidden" name="edit" id="edit" value="{{$edit}}">
            <input type="hidden" name="card_id" id="card_id" value="{{$card_id}}">
            <input type="hidden" name="carduser" id="carduser" value="{{$carduser_id}}">
            <input type="hidden" id="back_image" value="{{$card->名刺ファイル裏 ?? ''}}">

            <div class="card_regist_content">
                <div class="card_switch_container">
                    <div id="card_status" class="card_status" data-card_type="front">
                        表面
                    </div>
                    <div id="card_switch_button" class="card_switch_button">
                        <span>
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/change.svg') }}" alt="">
                        </span>
                        切り替え
                    </div>
                </div>
                <input type="file" name="card_file_front" id="card_file_front" class="card_file_input" data-card_type="front">
                <input type="file" name="card_file_back" id="card_file_back" class="card_file_input" data-card_type="back">
                <label for="card_file_front" id="card_file_front_label" class="card_file_label">
                    <div class="cropped_image_container">
                        <div class="cropped_image_container_text">
                            表 タップして名刺を読みこんでください
                        </div>
                    </div>
                </label>
                <label for="card_file_back" id="card_file_back_label" class="card_file_label display_none">
                    <div class="cropped_image_container">
                        <div class="cropped_image_container_text">
                            裏 タップして名刺を読みこんでください
                        </div>
                    </div>
                </label>
                <!-- <canvas id="canvas"></canvas> -->
                <div class="button_container">
                    <div class="send_button" id="send_button">
                        AI読み取り
                    </div>
                    <div class="crop_button">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/crop.svg') }}" alt="">
                        切り取り
                    </div>
                    <div class="remove_button display_none" id="remove_button">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/remove.svg') }}" alt="">
                        解除
                    </div>
                </div>
            </div>

            <div class="form_container personal_info">
                <table>
                    <tr>
                        <td>名前</td>
                        <td><input type="text" name="name" id="name" autocomplete="off" value="{{$card->名前 ?? ''}}"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>名前カナ</td>
                        <td><input type="text" name="name_kana" id="name_kana" autocomplete="off" value="{{$card->名前カナ ?? ''}}"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>携帯電話</td>
                        <td><input type="text" name="phone_number" id="phone_number" autocomplete="off" value="{{$card->携帯電話番号 ?? ''}}"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>メールアドレス</td>
                        <td><input type="text" name="email" id="email" autocomplete="off" value="{{$card->メールアドレス ?? ''}}"></td>
                        <td></td>
                    </tr>
                </table>
            </div>
            <div class="form_container company_info">
                <table>
                    <tr>
                        <td>会社名</td>
                        <td class="company_td">
                            <input type="text" name="company_name" id="company_name" autocomplete="off"
                                @if($card)
                                readonly class="company_choiced" value="{{$card->会社名 ?? ''}}"
                                @endif>
                        </td>
                        <td>
                            <img class="company_search_button" src="{{ asset(config('prefix.prefix').'/'.'img/card/search.svg') }}" alt="">
                        </td>
                    </tr>
                    <tr>
                        <td>会社名カナ</td>
                        <td>
                            <input type="text" name="company_name_kana" id="company_name_kana" autocomplete="off"
                                @if($card)
                                disabled value="{{$card->会社名カナ ?? ''}}"
                                @endif>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>拠点名</td>
                        <td id="branch_name_container">
                            @if($designate_branch)
                            <select class="branch_name_select" name="branch_id" id="branch_id">
                                @foreach($branches as $branch)
                                <option value="{{$branch->id}}" data-address="{{$branch->拠点所在地}}" data-phone_number="{{$branch->電話番号}}" data-fax_number="{{$branch->FAX番号}}" @if($branch->id == $card->拠点ID) selected @endif>{{$branch->拠点名}}</option>
                                @endforeach
                            </select>
                            <div class="add_branch_button">拠点追加</div>
  
                            @else
                            <input type="text" name="branch_name" id="branch_name" autocomplete="off">
                            @endif
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>所在地</td>
                        <td>
                            <input type="text" name="branch_address" id="branch_address" autocomplete="off"
                                @if($card)
                                disabled value="{{$card->拠点所在地 ?? ''}}"
                                @endif>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>電話番号</td>
                        <td>
                            <input type="text" name="branch_phone_number" id="branch_phone_number" autocomplete="off"
                                @if($card)
                                disabled value="{{$card->電話番号 ?? ''}}"
                                @endif>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>FAX番号</td>
                        <td>
                            <input type="text" name="branch_fax_number" id="branch_fax_number" autocomplete="off"
                                @if($card)
                                disabled value="{{$card->FAX番号 ?? ''}}"
                                @endif>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>

            <div class="form_container position_info">
                <table>
                    <tr>
                        <td>役職</td>
                        <td><input type="text" name="position" id="position" autocomplete="off" value="{{$card->役職 ?? ''}}"></td>
                        <td></td>
                    </tr>
                    @if(!$card)
                    <tr>
                        <td>部署1</td>
                        <td>
                            <input type="text" name="department1" class="department" id="department1" data-department_number="1" autocomplete="off">
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2"><button type="button" class="add_department_button" id="add_department" data-now_department_number="1">部署追加</button></td>
                    </tr>
                    @else
                    @foreach($card->departments as $index => $department)
                    <tr>
                        <td>部署{{$index+1}}</td>

                        <td>
                            <input type="text" name="department{{$index+1}}" class="department" id="department{{$index+1}}" data-department_number="{{$index+1}}" autocomplete="off" value="{{$department->部署名}}">
                        </td>
                        </td>
                        @if($index != 0)
                        <td>
                            <div class="delete_department_button">×</div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2"><button type="button" class="add_department_button" id="add_department" data-now_department_number="{{count($card->departments)}}">部署追加</button></td>
                    </tr>
                    @endif

                </table>
            </div>

        </form>
    </div>
</div>
@endsection

@section('footer')
@endsection