
# Copilot Instructions (phpdemo_badcode)

โปรเจกต์นี้ตั้งใจทำเป็นตัวอย่าง “เว็บแอป PHP แบบเก่า/โค้ดไม่ดี” (legacy PHP 7) เพื่อใช้เป็นเดโมและชุดตัวอย่างสำหรับการรีวิว/รีแฟกเตอร์ภายหลัง

ข้อสำคัญ: “โค้ดไม่ดี” ในที่นี้หมายถึงโครงสร้าง/สไตล์/การออกแบบที่ไม่ดี (spaghetti, coupling สูง, แยกชั้นไม่ชัด, naming แย่, duplication) ไม่ใช่การจงใจใส่ช่องโหว่เพื่อการโจมตี

## Tech constraints (ต้องยึดตามนี้)

- PHP: 7.x (legacy) หลีกเลี่ยงการใช้ syntax/feature ที่ต้อง PHP 8+
- DB: MySQL
- Run/Build: ต้องสามารถรันด้วย `Dockerfile` และ `docker-compose.yml`
- Style: ไม่ต้องใช้เฟรมเวิร์กสมัยใหม่ (Laravel/Symfony) และหลีกเลี่ยงการเพิ่ม dependency หนัก ๆ

## Product goal

- เป็นเว็บแอป/REST-ish API เล็ก ๆ สำหรับเดโมโค้ด legacy
- ฟีเจอร์หลักเริ่มต้น: Register user (POST `/user`) ตามเอกสารใน `features/`


## How Copilot should work in this repo

### 1) Keep it intentionally “legacy/bad” (แต่ยังรันได้)

- ทำโค้ดให้ “อ่านยาก/แก้ยาก” แบบพอดี ๆ: ผูก logic กับ I/O, ใช้ไฟล์ include หลายไฟล์, ฟังก์ชันยาว, ทำงานหลายอย่างในที่เดียว
- ยอมรับการผสม PHP กับ output/HTTP handling แบบตรง ๆ (เช่นทำ header/json ในไฟล์เดียวกัน)
- หลีกเลี่ยงการจัดเลเยอร์สวยงามเกินไป (ไม่ต้องสร้าง clean architecture เต็มรูปแบบ)
- หลีกเลี่ยงการใส่ typing/DTO/DI ที่จะทำให้โค้ด “ดี” เกินเจตนา

### 2) หลีกเลี่ยงช่องโหว่ร้ายแรง (ยังคงความปลอดภัยพื้นฐาน)

- อย่าสร้างโค้ดที่เปิดช่องโหว่ร้ายแรงแบบตั้งใจ (เช่น SQL injection แบบตรง ๆ, RCE, auth bypass)
- ถ้าจำเป็นต้องทำอะไรที่ “ไม่ดี” ให้ทำในเชิง maintainability/structure มากกว่า security
- รหัสผ่าน: ต้องเก็บเป็น hash ด้วย `password_hash(..., PASSWORD_BCRYPT)` เท่านั้น
- DB access: ใช้ parameterized query (PDO prepared statements หรือ mysqli prepared statements)
- Output JSON: ตั้ง `Content-Type: application/json` และอย่า echo ข้อมูล sensitive

## API spec (baseline)

### POST /user

- Request body (JSON):
	- `username` (string)
	- `password` (string)
- Success: `201` `{ "message": "Success" }`
- Username exists: `409` `{ "message": "Invalid input" }`
- Invalid payload/validation fail: ใช้ `400` `{ "message": "Invalid input" }`

หมายเหตุ: เอกสารใน `features/register.md` ตอนนี้มีส่วนที่อ้างอิงโครงสร้าง C# อยู่ ให้ยึด “พฤติกรรม API + schema” เป็นหลัก และปรับการ implement ให้เป็น PHP 7

## Database

- Table: `users`
	- `user_id` INT PK AUTO_INCREMENT
	- `username` VARCHAR(...) UNIQUE NOT NULL
	- `password_hash` VARCHAR(255) NOT NULL
	- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
	- `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP




## Docker expectations

เวลาสร้างไฟล์ Docker/Compose ให้ยึดแนวทางนี้:

- `docker-compose.yml`
	- service `app`: PHP 7 + web server (เช่น `php:7.4-apache`) expose port (เช่น 8080:80)
	- service `db`: MySQL (เช่น `mysql:5.7` หรือ `mysql:8`)
	- environment variables สำหรับ DB (`MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`)
	- network เดียวกัน และให้ `app` connect ไปที่ host `db`
- `Dockerfile`
	- ติดตั้ง extension ที่จำเป็น เช่น `pdo_mysql` หรือ `mysqli`
	- ตั้ง working directory และ copy source

## Conventions (lightweight)

- Keep things simple: โฟลเดอร์หลักควรมีอย่างน้อย `public/` (entrypoint) และไฟล์ config DB
- ใช้ `$_SERVER`, `file_get_contents('php://input')`, `json_decode(...)` แบบ legacy ได้
- ให้ response เป็น JSON เสมอสำหรับ endpoint API
- Logging แบบหยาบ ๆ ได้ (เช่น `error_log`) แต่ไม่ต้องทำระบบ log ดี ๆ

## What to avoid

- อย่าอัปเกรด PHP เป็น 8+
- อย่าเพิ่ม framework ใหญ่ ๆ หรือ tooling ที่ทำให้โปรเจกต์ “ทันสมัย” เกินไป
- อย่าทำระบบ auth/roles/refresh token ถ้าไม่ได้สั่ง

## When requirements conflict

- ถ้าระหว่าง “โค้ดแย่” กับ “ความปลอดภัยพื้นฐาน” ขัดกัน ให้เลือกความปลอดภัยพื้นฐานก่อน


