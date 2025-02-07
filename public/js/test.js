//---------------------------------------
// 1) OpenCV.js ロード完了時の処理
//---------------------------------------
function onOpenCvReady() {
  console.log("OpenCV.js is ready!");
  initCamera();
}

//---------------------------------------
// 2) カメラ起動
//---------------------------------------
function initCamera() {
  let video = document.getElementById("video");

  // デスクトップでのテストなら "user" 推奨
  // スマホ背面カメラなら "environment"
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

//---------------------------------------
// 3) キャプチャして名刺領域を検出 → トリミング・射影変換
//---------------------------------------
document.addEventListener("DOMContentLoaded", function() {
  document.getElementById("capture").addEventListener("click", function() {
      let video = document.getElementById("video");
      let canvas = document.getElementById("canvas");
      let outputCanvas = document.getElementById("output");

      // video の現在フレームを canvas に描画
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      let ctx = canvas.getContext("2d");
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      // OpenCV 用の Mat を取得 (RGBA)
      let src = new cv.Mat(canvas.height, canvas.width, cv.CV_8UC4);
      src.data.set(ctx.getImageData(0, 0, canvas.width, canvas.height).data);

      // ---- 以下、名刺検出フロー ----

      // 1) グレースケール化
      let gray = new cv.Mat();
      cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

      // 2) エッジ検出
      let edges = new cv.Mat();
      cv.Canny(gray, edges, 50, 150);

      // 3) 輪郭検出
      let contours = new cv.MatVector();
      let hierarchy = new cv.Mat();
      cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

      // 4) 一番大きい四角形を探す
      let biggest = null;
      let maxArea = 0;
      for (let i = 0; i < contours.size(); i++) {
          let cnt = contours.get(i);
          let area = cv.contourArea(cnt);
          // ある程度以上の面積だけ見る
          if (area > 1000) {
              let approx = new cv.Mat();
              // 輪郭を多角形近似
              cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);
              // 四角形かどうか
              if (approx.rows === 4 && area > maxArea) {
                  biggest = approx;
                  maxArea = area;
              } else {
                  approx.delete();
              }
          }
      }

      // 5) 四角形が見つかった場合のみ射影変換
      let dstWarped = null;
      if (biggest) {
          // 四隅の座標を取り出す
          let rect = biggest.data32S;
          // rect = [x0, y0, x1, y1, x2, y2, x3, y3]
          // 頂点の順番がバラバラの場合があるので、順序を整えてあげると精度が上がる
          // 簡易的にソートして左上→右上→右下→左下 の順に並べ直すなど
          // ここでは省略し、とりあえずそのまま使う

          // warpPerspective の行き先サイズを仮に 400 x 250 とかにする
          // (名刺なら横長の場合 400x250 くらい、縦長の場合は調整)
          let width = 400;
          let height = 250;

          let srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
              rect[0], rect[1],
              rect[2], rect[3],
              rect[4], rect[5],
              rect[6], rect[7]
          ]);
          let dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
              0, 0,
              width, 0,
              width, height,
              0, height
          ]);

          let M = cv.getPerspectiveTransform(srcTri, dstTri);
          dstWarped = new cv.Mat();
          cv.warpPerspective(src, dstWarped, M, new cv.Size(width, height), cv.INTER_LINEAR, cv.BORDER_CONSTANT, new cv.Scalar());

          // メモリ解放
          srcTri.delete();
          dstTri.delete();
          M.delete();
      }

      // 6) 名刺領域が検出できなかった場合はそのまま使う
      let finalMat = dstWarped ? dstWarped : src;

      // 7) 明るさ・コントラスト調整
      //   alpha=1.2(コントラスト), beta=30(明るさ) など適宜調整
      //   (ガンマ補正等を組み合わせる場合もある)
      let adjusted = new cv.Mat();
      let alpha = 1.2;
      let beta = 30;
      cv.convertScaleAbs(finalMat, adjusted, alpha, beta);

      // 8) outputCanvas に表示
      //   (検出成功なら射影変換後のサイズ、失敗なら元のサイズ)
      outputCanvas.width = adjusted.cols;
      outputCanvas.height = adjusted.rows;
      cv.imshow(outputCanvas, adjusted);

      // ---- メモリ解放 ----
      src.delete();
      gray.delete();
      edges.delete();
      contours.delete();
      hierarchy.delete();
      if (biggest) biggest.delete();
      if (dstWarped) dstWarped.delete();
      adjusted.delete();
  });
});
