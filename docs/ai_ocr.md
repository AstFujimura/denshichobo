# TAMERU AI OCR（帳簿保存）

## Ghostscript（PDF → JPEG 再試行）

欠落時ハイブリッド再試行で PDF を JPEG 化する際に `gs` が必要です。本番 Linux（dnf 系）では次を実行します。

```bash
sudo dnf install -y ghostscript
```

確認:

```bash
which gs
gs -version
```

パスを明示する場合のみ `.env` に設定します（通常は不要）。

```
GHOSTSCRIPT_PATH=/usr/bin/gs
```

再試行の ON/OFF: `TAMERU_AI_OCR_JPEG_RETRY`（既定 `true`）
