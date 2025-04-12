## Installation Guide

### Things You Need To Do:

1. **STEP 1:** After downloading "JordyMalik.rar":

   - Go to `C:\xampp\htdocs`
   - Left click "JordyMalik.rar"
   - Right click and select "Extract Here"

2. **STEP 2:**

   - Open "Xampp" if not already running (`C:\xampp\xampp-control.exe`)
   - Turn ON "Apache" and "MySQL"

3. **STEP 3:**

   - After both services are ON, click "ADMIN" next to MySQL

4. **STEP 4:**

   - When the browser opens to "phpMyAdmin"
   - Click "NEW"
   - For "Database name" enter "user_system_db"
   - Click "CREATE"

5. **STEP 5:**

   - Once created, look for "IMPORT" at the top
   - Click "Choose File"
   - Navigate to `C:\xampp\htdocs\JordyMalik\Jordan`
   - Select the file "user_system_db.sql"
   - Click "Open"

6. **STEP 6:**

   - Scroll down until you see "Import" button
   - Click it!

7. **STEP 7:**

   - Open your preferred code editor (VSCode, Notepad++, or Sublime)

8. **STEP 8:**

   - Open the "Jordan" folder (not "JordyMalik") in your code editor

9. **STEP 9:**

   - In your editor, navigate to:
     - `backend` → `database` → `config.php`
   - Check the `$host` value - you may need to update the port
   - Check "XAMPP Control Panel" for the correct "Port(s)" under "MySQL"
   - Update if needed

10. **STEP 10:**
    - You're now free to edit and modify everything as needed

## Important Resources

### Mailtrap (For Email OTP & Reset Password)

- URL: [https://mailtrap.io/signin](https://mailtrap.io/signin)
- Email: wizbulatespcccs@ptct.net
- Password: `PutangInaMo#123`
- Note: _Used for EMAIL OTP (Reset Password function)_

### QR Code References

- URL: [https://goqr.me/](https://goqr.me/)
- Note: `QR Code Generator`

- API URL: [https://goqr.me/api/](https://goqr.me/api/)
- Note: `Source of the QR Code API`

- Create QR Code Docs: [https://goqr.me/api/doc/create-qr-code/](https://goqr.me/api/doc/create-qr-code/)
- Note: `Reference for how to AUTO GENERATE QR Codes (you'll need to understand the backend connection implementation)`

- Read QR Code Docs: [https://goqr.me/api/doc/read-qr-code/](https://goqr.me/api/doc/read-qr-code/)
- Note: `Reference for the Scanner/Reader (implementation shows N/A until user scans)`
