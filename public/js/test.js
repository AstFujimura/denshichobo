// -------------------------------------
// OpenCV.js 読み込み完了後に呼ばれる
// -------------------------------------
function onOpenCvReady() {
  console.log("OpenCV.js is ready!");
  // OpenCV が使える状態になったら、あとは通常通りの処理を書ける
  initCamera();
}

function initCamera() {
  let video = document.getElementById("video");

  // カメラ起動 (デスクトップ開発時は "user" のほうが無難)
  navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
      .then(function (stream) {
          video.srcObject = stream;
          video.play();
          console.log("カメラ起動成功");
      })
      .catch(function (err) {
          console.error("カメラのアクセスエラー:", err);
      });
}

// DOM操作は jQuery でも生JS でもお好みで
document.addEventListener("DOMContentLoaded", function () {
  // キャプチャボタンクリック時の処理
  document.getElementById("capture").addEventListener("click", function() {
      let video = document.getElementById("video");
      let canvas = document.getElementById("canvas");
      let outputCanvas = document.getElementById("output");

      // video のサイズに合わせる
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;

      // canvas に現在の映像を描画
      let ctx = canvas.getContext("2d");
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      // OpenCVで画像取得
      let src = new cv.Mat(canvas.height, canvas.width, cv.CV_8UC4);
      src.data.set(ctx.getImageData(0, 0, canvas.width, canvas.height).data);

      // ---- まずはそのまま表示 ----
      // outputCanvas のサイズは適当に video と同じにしておく
      outputCanvas.width = canvas.width;
      outputCanvas.height = canvas.height;
      cv.imshow(outputCanvas, src);

      // メモリ解放
      src.delete();
  });
});
