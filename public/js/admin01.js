$(document).ready(function() {
  $('.hamburger01').click(function() {
    $('.sidebar01').toggleClass('sidebar01open'); // サイドバーの表示/非表示を切り替える
    $('.sidebarbatsu01').toggleClass('sidebarbatsuopen01'); // サイドバーの表示/非表示を切り替える
    // $('.main01').toggleClass('main01margin'); // サイドバーの表示/非表示を切り替える
  });
  $('.accordion1_01').click(function() {
      $('.accordion1content01').toggleClass('accordion1open'); // サイドバーの表示/非表示を切り替える
      $('.allow01').toggleClass('rotate');
    });
    $('.sidebarbatsu01').click(function() {
      $('.sidebar01').toggleClass('sidebar01open'); // サイドバーの表示/非表示を切り替える
      $('.sidebarbatsu01').toggleClass('sidebarbatsuopen01'); // サイドバーの表示/非表示を切り替える
      // $('.main01').toggleClass('main01margin'); // サイドバーの表示/非表示を切り替える
    });
});
