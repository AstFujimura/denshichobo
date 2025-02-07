let video = document.getElementById("video");
let canvas = document.getElementById("canvas");
let outputCanvas = document.getElementById("outputCanvas");
let ctx = canvas.getContext("2d");
let outputCtx = outputCanvas.getContext("2d");

// カメラを起動
navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(stream => {
        video.srcObject = stream;
    })
    .catch(err => console.error("カメラが利用できません", err));

// OpenCVが準備できたら処理開始
function onCvReady() {
    console.log("OpenCV.jsがロードされました");
}

// キャプチャボタン
document.getElementById("capture").addEventListener("click", function() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    let src = cv.imread(canvas); // OpenCVの画像として取得
    let processed = processImage(src); // 画像処理
    cv.imshow(outputCanvas, processed); // 結果を描画
    src.delete();
    processed.delete();
});

// 画像処理関数（輪郭検出 & 透視変換）
function processImage(src) {
    let gray = new cv.Mat();
    let blurred = new cv.Mat();
    let edges = new cv.Mat();
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();

    // 1. グレースケール変換
    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
    
    // 2. ぼかしを適用してノイズ除去
    cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
    
    // 3. Cannyエッジ検出
    cv.Canny(blurred, edges, 50, 150);

    // 4. 輪郭検出
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    let maxArea = 0, bestContour = null;
    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);
        let area = cv.contourArea(contour);
        if (area > maxArea) {
            let peri = cv.arcLength(contour, true);
            let approx = new cv.Mat();
            cv.approxPolyDP(contour, approx, 0.02 * peri, true);
            if (approx.rows === 4) { // 四角形なら候補
                maxArea = area;
                bestContour = approx;
            }
        }
    }

    let dst = new cv.Mat();
    if (bestContour) {
        let points = [];
        for (let i = 0; i < 4; i++) {
            points.push({
                x: bestContour.data32S[i * 2],
                y: bestContour.data32S[i * 2 + 1]
            });
        }

        // 4点を並び替え（左上、右上、右下、左下）
        points.sort((a, b) => a.x - b.x);
        let left = points.slice(0, 2).sort((a, b) => a.y - b.y);
        let right = points.slice(2, 4).sort((a, b) => a.y - b.y);
        let ordered = [left[0], right[0], right[1], left[1]];

        // 透視変換（名刺を正面表示に補正）
        let width = 400, height = 250;
        let srcMat = cv.matFromArray(4, 1, cv.CV_32FC2, ordered.flatMap(p => [p.x, p.y]));
        let dstMat = cv.matFromArray(4, 1, cv.CV_32FC2, [0, 0, width, 0, width, height, 0, height]);
        let M = cv.getPerspectiveTransform(srcMat, dstMat);
        cv.warpPerspective(src, dst, M, new cv.Size(width, height));

        srcMat.delete();
        dstMat.delete();
        M.delete();
    } else {
        dst = src.clone();
    }

    gray.delete();
    blurred.delete();
    edges.delete();
    contours.delete();
    hierarchy.delete();
    
    return dst;
}
