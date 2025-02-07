let video = document.getElementById("video");
let canvas = document.getElementById("canvas");
let ctx = canvas.getContext("2d");
let outputCanvas = document.getElementById("output");
let outputCtx = outputCanvas.getContext("2d");

// カメラを起動
navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(stream => {
        video.srcObject = stream;
    })
    .catch(err => console.error("カメラのアクセスエラー:", err));

// OpenCV 読み込み後の処理
function onOpenCvReady() {
    console.log("OpenCV.js Loaded");
}

// キャプチャボタンのクリックイベント
document.getElementById("capture").addEventListener("click", function () {
    // Canvas にカメラ映像を描画
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // OpenCV.js を使って画像処理
    let src = cv.imread(canvas);
    let dst = new cv.Mat();

    // グレースケール変換
    cv.cvtColor(src, dst, cv.COLOR_RGBA2GRAY, 0);

    // Canny エッジ検出
    let edges = new cv.Mat();
    cv.Canny(dst, edges, 50, 150);

    // 輪郭検出
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    // 最大の四角形を探す
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
        // 射影変換で補正
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

        // 出力Canvasに表示
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
