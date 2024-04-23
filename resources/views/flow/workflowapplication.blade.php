@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>



@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="title_icon">ワークフロー申請</h2>
    <form action="{{route('workflowapplicationpost')}}" method="post" id="flow_application_form" class="flow_application_form" enctype="multipart/form-data">
        @csrf

        <div class="flow_application_container">
            <div class="flow_application_button_content">
                <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    トップへもどる
                </a>
                <button class="next_button flow_next_button" id="flow_next_button">
                    次へ
                </button>
            </div>
            <div class="flow_application_main_content">


                <div class="category_choice_container">
                    <div class="flow_application_title">
                        カテゴリ選択
                    </div>
                    @foreach ($m_categories as $m_category)
                    <input type="radio" name="category" id="category{{$m_category->id}}" value="{{$m_category->id}}" class="category_input">
                    <label for="category{{$m_category->id}}" class="category_element">
                        <div class="category_check"></div>
                        <div class="category_name">
                            {{$m_category->カテゴリ名}}
                        </div>
                    </label>
                    @endforeach

                </div>

                <div class="flow_application_content">

                    <div class="flow_application_title">
                        申請内容
                    </div>
                    <div class="flow_application_area">
                        <div class="flow_application_area_message">
                            カテゴリを選択すると<br>
                            申請項目が表示されます
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>




    </form>

</div>
@endsection

@section('footer')
@endsection