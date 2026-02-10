# Tesseract OCR Setup (Identity Document Extraction)

The "Extract data" feature from identity document images requires **Tesseract OCR** to be installed on the machine running the Laravel app.

## Windows

1. **Download** the installer from:  
   https://github.com/UB-Mannheim/tesseract/wiki  

2. **Run** the installer (e.g. `tesseract-ocr-w64-setup-5.x.x.exe`).

3. **Optional – add to PATH**  
   If you add Tesseract to the system PATH, no extra config is needed.

4. **If not in PATH** – set the executable path in `.env` (use **forward slashes** so the .env parser does not treat backslashes as escape characters):
   ```env
   TESSERACT_EXECUTABLE="C:/Program Files/Tesseract-OCR/tesseract.exe"
   ```
   Use the actual path where you installed Tesseract (e.g. `C:/Program Files/Tesseract-OCR/` or `C:/Users/<You>/AppData/Local/Programs/Tesseract-OCR/`).

5. **Arabic + English**  
   During setup, ensure **Additional Language Data** includes **Arabic** (and English is default).  
   If you already installed without Arabic, run the installer again and add the Arabic language pack.

6. Restart the PHP server / queue workers after changing `.env`.

## Linux (e.g. Ubuntu)

```bash
sudo apt update
sudo apt install tesseract-ocr tesseract-ocr-ara
```

No `.env` change needed if `tesseract` is in PATH.

## Verify

**From the project (recommended):**
```bash
php artisan tesseract:check
```
This checks the path from `.env` and prints the Tesseract version if OK.

**From a terminal (without Laravel):**
- **Windows (PowerShell):**  
  `& "C:\Program Files\Tesseract-OCR\tesseract.exe" --version`
- **If in PATH:**  
  `tesseract --version`

You should see a version line (e.g. `tesseract 5.x.x`).

**If "Extract data" still fails:** set `APP_DEBUG=true` in `.env`, try again, and read the full error message shown on the page (e.g. missing Arabic language data → install "Additional language data" / `ara` and rerun the installer, or the app will fall back to English-only OCR).
