@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>



@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="stamp_setting"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/stamp.svg') }}" alt="" class="title_icon">印鑑設定</h2>
    <form action="{{route('workflowstamppost')}}" id="stamp_regist" class="stamp_regist" method="post" enctype="multipart/form-data">
        @csrf
        <div class="flow_stamp_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
            <button class="stamp_regist_button" id="stamp_regist_button">
                登録
            </button>
        </div>
        <div class="flow_stamp_main_container">
            <!-- <div class="flow_stamp_left_container">

        </div> -->
            <div class="flow_stamp_middle_container">
                <div class="flow_stamp_letter_content">
                    @if ($m_stamp)
                    <input type="text" class="flow_stamp_letter" id="flow_stamp_letter" placeholder="(例) 佐藤" value="{{$m_stamp->文字}}">
                    @else
                    <input type="text" class="flow_stamp_letter" id="flow_stamp_letter" placeholder="(例) 佐藤" value="">
                    @endif
                    <div class="flow_stamp_lettter_change_button">
                        変更
                    </div>
                </div>
                <div class="flow_stamp_preview">
                </div>
            </div>
            <div class="flow_stamp_right_container">
                <div class="flow_stamp_font_container">
                    <div class="flow_stamp_right_subtitle">
                        フォント
                    </div>

                    <div class="flow_stamp_font_property">
                        <select class="flow_stamp_font_select" name="font" id="flow_stamp_font_select">
                            @if ($m_stamp)
                            <option value="HG正楷書体" {{$m_stamp->フォント == "HG正楷書体" ? 'selected' : ''}}>楷書体</option>
                            <option value="HGゴシック体" {{$m_stamp->フォント == "HGゴシック体" ? 'selected' : ''}}>ゴシック</option>
                            <option value="HGR明朝体" {{$m_stamp->フォント == "HGR明朝体" ? 'selected' : ''}}>明朝</option>
                            @else
                            <option value="HG正楷書体">楷書体</option>
                            <option value="HGゴシック体">ゴシック</option>
                            <option value="HGR明朝体">明朝</option>

                            @endif
                        </select>
                    </div>
                    <div class="flow_font_size_title">
                        フォントサイズ
                    </div>
                    @if ($m_stamp)
                    <input type="range" name="font_size" min="0" max="400" value="{{$m_stamp->フォントサイズ}}" step="1" class="stamp_slider font_size_slider">
                    @else
                    <input type="range" name="font_size" min="0" max="400" value="245" step="1" class="stamp_slider font_size_slider">
                    @endif

                    <div class="flow_font_size_title">
                        縦横比
                    </div>
                    @if ($m_stamp)
                    <input type="range" min="-0.4" max="0.4" value="{{$m_stamp->縦横比}}" step="0.01" class="stamp_slider scale_slider">
                    @else
                    <input type="range" min="-0.4" max="0.4" value="0.2" step="0.01" class="stamp_slider scale_slider">
                    @endif

                    <input type="hidden" name="aspect" id="aspect" value="">
                </div>
                <div class="letter_container">
                    @if ($m_stamp)
                    <input type="hidden" id="letter_length" value="{{$m_stamp->文字数}}" name="letter_length">
                    @else
                    <input type="hidden" id="letter_length" value="0" name="letter_length">
                    @endif

                    @if ($m_stamp)
                    @foreach($m_stamp_chars as $m_stamp_char)
                    <div class="flow_stamp_str_container">
                        <div class="flow_stamp_right_subtitle" data-str_num="{{$loop->index}}">
                            {{$m_stamp_char->文字}}
                        </div>
                        <input type="hidden" class="flow_stamp_char" name="char{{$loop->index}}" value="{{$m_stamp_char->文字}}" data-str_num="{{$loop->index}}">
                        <div class="flow_font_size_title">
                            X方向
                        </div>
                        <input type="range" name="x{{$loop->index}}" min="-50" max="450" value="{{$m_stamp_char->left}}" step="1" class="stamp_slider x_slider" data-str_num="{{$loop->index}}">
                        <div class="flow_font_size_title">
                            Y方向
                        </div>
                        <input type="range" name="y{{$loop->index}}" min="-50" max="450" value="{{$m_stamp_char->top}}" step="1" class="stamp_slider y_slider" data-str_num="{{$loop->index}}">
                    </div>
                    @endforeach
                    @endif

                </div>
            </div>
        </div>
        <input type="hidden" id="stamp_img" name="stamp_img" value="">
        <input type="hidden" id="毛筆体" value="{{ asset(config('prefix.prefix').'/'.'font/毛筆体.ttf') }}">
        <input type="hidden" id="HG正楷書体" value='https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/HG正楷書体.TTF'>
        <input type="hidden" id="HGゴシック体" value='https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/HGゴシック体.TTC'>
        <input type="hidden" id="HGR明朝体" value='https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/HGR明朝体.TTC'>
        <input type="hidden" name="t_flow" value="{{$t_flow_id}}">
        <input type="hidden" name="t_approval" value="{{$t_approval_id}}">
    </form>
</div>
@endsection

@section('footer')
@endsection