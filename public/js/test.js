$(document).ready(function () {
  let video = $("#video")[0];
  let canvas = $("#canvas")[0];
  let ctx = canvas.getContext("2d");
  let outputCanvas = $("#output")[0];
  let outputCtx = outputCanvas.getContext("2d");

  // 📌 カメラ起動
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
          .then(function (stream) {
              video.srcObject = stream;
          })
          .catch(function (err) {
              console.error("カメラのアクセスエラー:", err);
          });
  } else {
      console.error("getUserMedia がサポートされていません。");
  }

  // 📌 OpenCV 読み込み完了時の処理
  window.onOpenCvReady = function () {
      console.log("OpenCV.js がロードされました。");
  };

  // 📌 キャプチャ処理
  $("#capture").click(function () {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      let src = cv.imread(canvas);
      let dst = new cv.Mat();

      // 🟢 グレースケール化
      cv.cvtColor(src, dst, cv.COLOR_RGBA2GRAY, 0);

      // 🟢 エッジ検出（Canny）
      let edges = new cv.Mat();
      cv.Canny(dst, edges, 50, 150);

      // 🟢 輪郭検出
      let contours = new cv.MatVector();
      let hierarchy = new cv.Mat();
      cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

      // 🟢 最大の四角形を探す
      let biggest = null;
      let maxArea = 0;
      for (let i = 0; i < contours.size(); i++) {
          let cnt = contours.get(i);
          let area = cv.contourArea(cnt, false);
          if (area > maxArea) {
              let approx = new cv.Mat();
              cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);
              if (approx.rows === 4) {
                  biggest = approx;
                  maxArea = area;
              }
          }
      }

      if (biggest) {
          // 🟢 射影変換（トリミング）
          let rect = biggest.data32S;
          let srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
              rect[0], rect[1],
              rect[2], rect[3],
              rect[4], rect[5],
              rect[6], rect[7]
          ]);

          let dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [0, 0, 300, 0, 300, 400, 0, 400]);
          let M = cv.getPerspectiveTransform(srcTri, dstTri);
          let dstWarped = new cv.Mat();
          cv.warpPerspective(src, dstWarped, M, new cv.Size(300, 400));

          // 🟢 出力Canvasに表示
          outputCanvas.width = 300;
          outputCanvas.height = 400;
          cv.imshow(outputCanvas, dstWarped);

          // メモリ解放
          srcTri.delete();
          dstTri.delete();
          M.delete();
          dstWarped.delete();
      }

      // メモリ解放
      src.delete();
      dst.delete();
      edges.delete();
      contours.delete();
      hierarchy.delete();
  });
});
