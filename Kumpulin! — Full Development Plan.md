# KUMPULIN! — FULL DEVELOPMENT PLAN

## 1. PROJECT OVERVIEW

Build a modern web application named **Kumpulin!** using Laravel and Tailwind CSS.

Kumpulin! is an **AI-assisted assignment submission and validation platform**.

The core concept:

- Students do NOT need to create an account.
- Students access an assignment session using a session link/code.
- Students fill in their identity:
  - Nama Lengkap
  - Kelas
  - Jurusan
  - Catatan
- Students upload their assignment as an image.
- The backend sends the uploaded image and the session's Source of Truth to Arnaru-AI.
- AI analyzes the assignment and provides a recommendation.
- AI does NOT make the final decision.
- Admin reviews the student's submission and AI analysis.
- Admin determines the final status.
- Students can periodically check their submission status using a unique Submission ID.

The most important principle is:

**Source of Truth → AI Recommendation → Admin Final Decision**

AI is an assistant for Admin, not the final judge.

---

# 2. TECH STACK

Use:

- Laravel
- PHP
- Blade
- Tailwind CSS
- Vite
- MySQL / MariaDB
- Laravel HTTP Client
- Laravel Queue
- Laravel Storage
- Lucide Icons

Do NOT introduce unnecessary frontend frameworks such as React, Vue, or Livewire unless there is a strong technical reason.

Prefer:

- Laravel Blade
- Server-side rendering
- Tailwind CSS
- Vanilla JavaScript only for small interactions

The application should be lightweight, clean, maintainable, and easy to understand.

---

# 3. DESIGN DIRECTION

Kumpulin! should feel like a:

**Modern SaaS + Student Utility**

Design characteristics:

- Modern
- Clean
- Minimal
- Friendly
- Trustworthy
- Professional
- Lots of whitespace
- Strong visual hierarchy
- Mobile-first
- Lightweight

Do NOT make it look like:

- An old-school school administration system
- A generic Bootstrap dashboard
- A childish education app
- An overly futuristic AI interface

AI should not dominate the student UI.

From the student's perspective, the product is simply:

**Submit → Track → Done**

---

# 4. BRANDING

Application name:

**Kumpulin!**

Primary color:

```text
#2563EB
```

Primary palette:

```text
Primary 50:  #EFF6FF
Primary 100: #DBEAFE
Primary 500: #3B82F6
Primary 600: #2563EB
Primary 700: #1D4ED8
```

Neutral palette:

```text
White:    #FFFFFF
Slate 50: #F8FAFC
Slate 100:#F1F5F9
Slate 200:#E2E8F0
Slate 500:#64748B
Slate 700:#334155
Slate 900:#0F172A
```

Semantic colors:

```text
Success: #16A34A
Warning: #F59E0B
Danger:  #DC2626
Info:    #2563EB
```

Typography:

Use **Plus Jakarta Sans**.

Font weights:

- 400 Regular
- 500 Medium
- 600 Semibold
- 700 Bold

Border radius:

- Cards: 12px
- Inputs: 10px
- Buttons: 10px
- Badges: fully rounded

Use subtle borders and shadows.

Avoid excessive gradients, glassmorphism, and huge shadows.

---

# 5. ICON SYSTEM

Use **Lucide Icons** consistently.

Do NOT mix Lucide with Font Awesome or another icon library.

Recommended icons:

```text
Dashboard       LayoutDashboard
Sessions        ClipboardList
Submission      FileUp
Upload          Upload
Search          Search
Completed       CircleCheck
Pending         Clock3
AI              Sparkles
Review          ClipboardCheck
Delete          Trash2
Back            ArrowLeft
Copy            Copy
Settings        Settings
User            User
Calendar        Calendar
Book            BookOpen
Warning         TriangleAlert
Error           CircleX
Refresh         RefreshCw
```

Icons should support text, not replace important labels.

---

# 6. USER ROLES

There are two main user types.

## Admin

Admin can:

- Login
- Create assignment sessions
- Edit sessions
- Manage Source of Truth
- View submissions
- View AI analysis
- Review submissions
- Determine final status
- Add admin notes
- View submission history
- View session statistics

## Student

Students:

- Do NOT login
- Do NOT have accounts
- Do NOT have passwords
- Access a session using its public session code/link
- Fill in submission information
- Upload assignment image
- Receive Submission ID
- Check status using Submission ID

---

# 7. CORE DATA FLOW

The main flow must be:

```text
ADMIN
  ↓
Create Assignment Session
  ↓
Input Source of Truth
  ↓
Generate Session Code
  ↓
STUDENT
  ↓
Open Session
  ↓
Fill Student Information
  ↓
Upload Assignment Photo
  ↓
Create Submission
  ↓
AI Processing
  ↓
AI Validation Result
  ↓
Admin Review
  ↓
Admin Final Decision
  ↓
Student Checks Status
```

---

# 8. ASSIGNMENT SESSION

Every assignment is a separate Session.

Examples:

```text
Tugas Informatika
29 September 2026
```

and:

```text
Tugas Matematika
30 September 2026
```

Each Session has its own Source of Truth and submissions.

Example:

```text
Session Code:
TSK-INF-290926-X82K
```

Student submission belongs to exactly one session.

---

# 9. SESSION DATA

Each assignment session should contain:

- ID
- Session Code
- Title
- Subject
- Description
- Assignment Date
- Status
- Created By
- Created At
- Updated At

Possible session statuses:

```text
active
closed
archived
```

Only active sessions can accept new submissions.

---

# 10. SOURCE OF TRUTH

Source of Truth is the reference material used by AI to analyze student submissions.

For MVP, support a structured Source of Truth.

Example:

```text
Question 1:
Jelaskan pengertian algoritma.

Expected Answer / Concept:
- langkah sistematis
- menyelesaikan masalah
- urutan instruksi
```

Another:

```text
Question 2:
Buat flowchart proses login.

Expected:
- Start
- Input username
- Input password
- Validation
- Success / Failure
- End
```

Source of Truth should support:

- Question
- Expected Answer
- Validation Type
- Required Concepts
- Order

Validation types:

```text
exact
semantic
visual
checklist
```

Do not force every assignment to use exact answer matching.

---

# 11. SOURCE OF TRUTH VERSIONING

Source of Truth should have versions.

Example:

```text
Source of Truth v1
Source of Truth v2
```

When AI validates a submission, save the Source of Truth version used.

This is important for auditability.

If the admin changes the Source of Truth later, older AI results must still be traceable to the original version.

---

# 12. STUDENT SUBMISSION FORM

Students do not need login.

The submission form must contain exactly these main fields:

### Nama Lengkap

Required.

### Kelas

Required.

### Jurusan

Required.

### Catatan

Optional.

### Upload Tugas

Required.

Supported format for MVP:

- JPG
- JPEG
- PNG
- WEBP if supported safely

Recommended maximum file size:

10 MB per image.

The system should validate MIME type and actual uploaded file.

---

# 13. MULTIPLE IMAGE SUPPORT

Design the backend so multiple images can be supported later.

For MVP, one assignment image is acceptable.

However, avoid hard-coding the database structure so tightly that supporting multiple images later becomes impossible.

If multiple images are implemented:

```text
Submission
   ↓
Submission Files
   ├── image 1
   ├── image 2
   └── image 3
```

The Arnaru-AI API supports up to 9 files, so this can later be utilized.

---

# 14. SUBMISSION ID

Every submission must receive a unique public Submission ID.

Example:

```text
KMP-8F42A91X
```

This ID is different from the database primary key.

The student uses this public ID to check status.

Do NOT expose sequential database IDs publicly.

---

# 15. STUDENT SUBMISSION SUCCESS

After submitting:

Display:

```text
✓

Tugas Berhasil Dikumpulkan!

Submission ID

KMP-8F42A91X

Simpan ID ini untuk mengecek
status tugas kamu.

[ Salin ID ]

[ Cek Status ]
```

The Submission ID must be easy to copy.

---

# 16. STUDENT STATUS CHECK

Students can visit:

```text
/check
```

and enter:

```text
KMP-8F42A91X
```

or use a direct URL:

```text
/check/KMP-8F42A91X
```

Do not require login.

---

# 17. STUDENT-FACING STATUS

Students should only see safe/public status information.

Possible statuses:

```text
processing
pending_review
completed
incomplete
revision_required
```

Student-facing descriptions:

### Processing

```text
Sedang diproses

Tugas kamu sedang dianalisis.
```

### Pending Review

```text
Sedang ditinjau Admin

Analisis AI telah selesai dan
submission sedang menunggu validasi Admin.
```

### Completed

```text
✓ Tugas Selesai

Tugas kamu telah divalidasi oleh Admin.
```

### Incomplete

```text
Tugas Belum Selesai

Silakan periksa kembali tugas kamu.
```

### Revision Required

```text
Perlu Perbaikan

Terdapat bagian yang perlu diperbaiki.
```

If Admin provides a public note, display it.

Do NOT expose:

- System prompt
- Raw AI response
- Internal API data
- Internal error details
- AI prompt
- Internal confidence unless intentionally designed as public
- Admin-only information

---

# 18. STUDENT STATUS TIMELINE

Use a visual timeline.

Example:

```text
✓ Tugas Dikumpulkan
        │
        ▼
✓ AI Memproses
        │
        ▼
● Review Admin
        │
        ▼
○ Selesai
```

Use Lucide icons and semantic colors.

The current state should be visually obvious.

---

# 19. AI INTEGRATION

Use the provided Arnaru-AI API.

Endpoint:

```text
POST /api/chat
```

Base URL:

```text
https://arnaru-ai.vercel.app
```

Image requests must use:

```text
multipart/form-data
```

Fields:

```text
question
model
systemPrompt
files[]
```

Example conceptual request:

```text
POST https://arnaru-ai.vercel.app/api/chat

question = assignment validation instructions
model = selected AI model
systemPrompt = AI validator instructions
files[] = student assignment image
```

Use Laravel's HTTP Client for this integration.

Do NOT call Arnaru-AI directly from browser JavaScript.

---

# 20. AI SERVICE ARCHITECTURE

Create a dedicated service:

```text
app/Services/ArnaruAIService.php
```

Do NOT put the entire AI integration directly inside the controller.

The service should be responsible for:

- Building the AI prompt
- Building the system prompt
- Selecting the AI model
- Sending the image
- Calling Arnaru-AI
- Handling HTTP errors
- Parsing response
- Returning normalized AI result

Controller should remain focused on application flow.

---

# 21. AI SYSTEM PROMPT

The AI should be explicitly instructed:

```text
You are an AI assignment validation assistant.

Your role is to analyze a student's assignment
against the provided Source of Truth.

You are NOT the final decision maker.

Your result is only a recommendation for an administrator.

Analyze:
1. Whether required questions are answered.
2. Whether answers match the Source of Truth.
3. Whether answers appear incorrect.
4. Whether parts of the image are unreadable.
5. Confidence for each analysis.
6. Overall recommendation.

Never claim that the assignment is officially approved.

Return structured JSON only.
```

Adapt the prompt dynamically based on the Session Source of Truth.

---

# 22. AI QUESTION PROMPT

The question sent to the API should contain:

```text
Assignment Information

Title:
...

Subject:
...

Source of Truth:
...

Instructions:

Analyze the attached student assignment image.

Compare the student's answers with the Source of Truth.

Identify:
- answered questions
- missing questions
- incorrect answers
- uncertain answers
- unreadable areas

Return the requested structured JSON.

Remember:
The administrator makes the final decision.
```

Do not blindly trust AI output.

---

# 23. AI RESPONSE STRUCTURE

Normalize the AI result into a structure similar to:

```json
{
  "overall_result": "likely_completed",
  "confidence": 0.92,
  "questions": [
    {
      "number": 1,
      "status": "correct",
      "confidence": 0.97,
      "reason": "Answer matches the expected concept."
    },
    {
      "number": 2,
      "status": "uncertain",
      "confidence": 0.61,
      "reason": "The answer is partially unreadable."
    }
  ],
  "missing_questions": [],
  "unreadable_questions": [],
  "summary": "Most required answers were detected."
}
```

Possible AI overall results:

```text
likely_completed
likely_incomplete
uncertain
error
```

AI result is NOT the final submission status.

---

# 24. AI CONFIDENCE

AI confidence is NOT the student's academic score.

Example:

```text
AI Confidence: 92%
```

means AI's confidence in its analysis.

Do NOT display:

```text
Nilai: 92
```

unless a separate grading system is explicitly implemented.

For MVP, Kumpulin! is a validation system, not an automatic grading system.

---

# 25. AI DATABASE RECORD

Create an `ai_validations` table containing:

```text
id
submission_id
model
status
result
confidence
analysis
raw_response
source_of_truth_version
started_at
completed_at
created_at
updated_at
```

Save the raw response for debugging and auditing.

---

# 26. AI PROCESSING

Do NOT make the student wait for the entire AI request if avoidable.

Recommended flow:

```text
Student Upload
      ↓
Create Submission
      ↓
Status = processing
      ↓
Dispatch Laravel Job
      ↓
Arnaru-AI
      ↓
Save AI Validation
      ↓
Submission Status = pending_review
```

Use:

```text
app/Jobs/ValidateSubmissionWithAI.php
```

Use Laravel Queue.

The student should immediately receive the Submission ID after successful upload.

---

# 27. ADMIN FINAL DECISION

Admin must explicitly decide the final status.

Available decisions:

```text
completed
incomplete
revision_required
```

The Admin Review must contain:

- Admin ID
- Submission ID
- Decision
- Admin Note
- Reviewed At

Create:

```text
admin_reviews
```

Table fields:

```text
id
submission_id
admin_id
decision
note
reviewed_at
created_at
updated_at
```

---

# 28. ADMIN DASHBOARD

Admin dashboard should show summary statistics:

```text
Total Sessions
Pending Review
Processing
Completed
Revision Required
```

Use clean statistic cards.

Example:

```text
┌──────────────┐
│ 38           │
│ Pending      │
│ Review       │
└──────────────┘
```

Avoid excessive charts for MVP.

---

# 29. SESSION MANAGEMENT

Admin should be able to:

- Create Session
- View Session
- Edit Session
- Close Session
- Archive Session

Session list should display:

```text
Title
Subject
Date
Status
Submission Count
Pending Review
Action
```

Example:

```text
Tugas Informatika
29 September 2026
32 submissions
12 pending review

[ Buka ]
```

---

# 30. SESSION DETAIL

Admin opens a session:

```text
Tugas Informatika
29 September 2026

Session Code:
TSK-INF-290926-X82K
```

Show:

```text
Total Submissions
Pending Review
Completed
Incomplete
Revision Required
```

Then submission table:

```text
Submission ID
Student
Class
Major
AI Result
Admin Status
Submitted At
Action
```

---

# 31. ADMIN REVIEW PAGE

Use a desktop split-view layout.

Left:

Student assignment image.

Right:

Submission information.

Example:

```text
Submission
KMP-8F42A91X

Zaky Fardhan
X RPL 1
Rekayasa Perangkat Lunak

----------------------------

AI Analysis

LIKELY COMPLETED

Confidence
92%

Question 1
✓ Correct

Question 2
✓ Correct

Question 3
⚠ Uncertain

----------------------------

Admin Decision

[ Selesai ]
[ Belum Selesai ]
[ Perlu Perbaikan ]

Admin Note

[.........................]

[ Simpan Validasi ]
```

The admin should be able to inspect the actual uploaded image while reviewing the AI analysis.

---

# 32. ADMIN VS AI STATUS

Keep these separate.

Example:

```text
AI Result:
likely_completed

Admin Decision:
incomplete
```

This is valid.

AI can be wrong.

Never overwrite the AI result with the Admin decision.

Both must be stored independently.

---

# 33. DATABASE STRUCTURE

Recommended tables:

```text
users

assignment_sessions

source_of_truths

submissions

submission_files

ai_validations

admin_reviews
```

For MVP, if multiple files are not implemented yet, `submission_files` can still be prepared for future scalability.

---

# 34. RELATIONSHIPS

Relationships:

```text
User
  hasMany AssignmentSession

AssignmentSession
  belongsTo User
  hasOne/current SourceOfTruth
  hasMany Submissions

SourceOfTruth
  belongsTo AssignmentSession

Submission
  belongsTo AssignmentSession
  hasMany SubmissionFiles
  hasMany AIValidations
  hasMany AdminReviews

AIValidation
  belongsTo Submission

AdminReview
  belongsTo Submission
  belongsTo User
```

Use Eloquent relationships properly.

Avoid unnecessary manual joins where relationships are more appropriate.

---

# 35. ROUTING

Suggested public routes:

```text
GET  /
GET  /submit/{sessionCode}
POST /submit/{sessionCode}
GET  /submission/{submissionCode}
GET  /check
GET  /check/{submissionCode}
```

Suggested admin routes:

```text
GET    /admin/dashboard

GET    /admin/sessions
GET    /admin/sessions/create
POST   /admin/sessions
GET    /admin/sessions/{session}
GET    /admin/sessions/{session}/edit
PUT    /admin/sessions/{session}
PATCH  /admin/sessions/{session}/close

GET    /admin/submissions
GET    /admin/submissions/{submission}
POST   /admin/submissions/{submission}/review
```

Use route names consistently.

Example:

```text
route('student.submit', ...)
route('student.check')
route('admin.dashboard')
route('admin.sessions.index')
route('admin.submissions.show')
```

---

# 36. ADMIN AUTHENTICATION

Admin authentication is required.

Students do not authenticate.

Use Laravel's authentication system.

Admin routes must be protected with authentication middleware.

Do not expose admin routes publicly.

---

# 37. SECURITY

Important requirements:

### Upload Security

Validate:

- MIME type
- Extension
- File size
- Actual file content

Do not trust the original filename.

Generate safe server-side filenames.

Store uploads through Laravel Storage.

Do not directly expose sensitive filesystem paths.

### Authorization

Admin actions must require authenticated admin access.

Students must only be able to view public information for their own Submission ID.

### Session Code

Use unpredictable session codes.

### Submission Code

Use unpredictable public identifiers.

Never expose auto-increment database IDs publicly.

### AI API

API credentials must be stored in `.env`.

Example:

```env
ARNARU_AI_BASE_URL=https://arnaru-ai.vercel.app
ARNARU_AI_MODEL=gpt-5.5
ARNARU_AI_API_KEY=...
```

Never expose credentials in Blade or JavaScript.

---

# 38. ENVIRONMENT CONFIGURATION

Create configuration through:

```text
config/services.php
```

Example:

```text
'arnaru' => [
    'base_url' => env('ARNARU_AI_BASE_URL'),
    'model' => env('ARNARU_AI_MODEL', 'gpt-5.5'),
    'api_key' => env('ARNARU_AI_API_KEY'),
],
```

Do not hard-code the API URL/model inside controllers.

---

# 39. ERROR HANDLING

AI can fail.

Possible cases:

```text
API unavailable
Timeout
Invalid response
Unsupported image
Malformed JSON
Rate limit
Server error
```

If AI fails:

```text
Submission status:
processing / ai_error
```

Admin should see:

```text
AI Validation Failed

The AI analysis could not be completed.

[ Retry Analysis ]
```

Do not show raw exceptions to students.

---

# 40. RETRY AI

Admin should be able to retry failed AI validation.

Flow:

```text
AI Error
   ↓
Admin clicks Retry
   ↓
Dispatch Job
   ↓
AI Processing
   ↓
New AI Validation record
```

Do not overwrite previous AI validation records if possible.

Keep history.

---

# 41. AUDITABILITY

The system should preserve:

- Submission timestamp
- Source of Truth version
- AI model used
- AI result
- AI confidence
- AI raw response
- Admin decision
- Admin note
- Admin review timestamp

This makes the system easier to debug and trust.

---

# 42. STUDENT UI PAGES

Required pages:

```text
Landing Page
Session Submission Page
Submission Success Page
Status Check Page
Submission Status Detail
```

Student UI should be extremely simple.

No sidebar.

No complicated navigation.

---

# 43. ADMIN UI PAGES

Required pages:

```text
Login
Dashboard
Session List
Create Session
Edit Session
Session Detail
Submission List
Submission Review
```

Optional later:

```text
Settings
AI Configuration
Audit Logs
```

---

# 44. RESPONSIVE DESIGN

Mobile-first.

Student experience should prioritize mobile because students are likely to upload assignment photos from smartphones.

Desktop Admin experience should prioritize information density.

Student mobile layout:

```text
Header
↓
Session Information
↓
Student Form
↓
Upload
↓
Submit
```

Admin desktop:

```text
Sidebar
+
Content Area
```

Admin mobile:

```text
Compact Header
+
Content
```

Use responsive Tailwind utilities.

Do not create a separate mobile application.

---

# 45. UPLOAD UX

The upload component should have three states:

### Empty

```text
Upload Tugas

Klik untuk memilih foto
JPG, PNG • Maks. 10 MB
```

### Selected

```text
[ Thumbnail ]

tugas.jpg
2.4 MB

✓ Siap dikumpulkan
```

### Uploading

```text
Uploading...
██████████░░░░
```

Disable duplicate submission while upload is processing.

---

# 46. LOADING STATES

Use skeletons or spinners appropriately.

Do not freeze the interface.

Examples:

```text
Loading Sessions...
Processing AI...
Saving Review...
```

Use Lucide `LoaderCircle` with subtle animation where appropriate.

---

# 47. EMPTY STATES

Every major list needs an intentional empty state.

Example:

```text
Belum Ada Session

Buat session tugas pertama untuk
mulai menerima submission.

[ Buat Session ]
```

Submission empty state:

```text
Belum ada tugas yang dikumpulkan.
```

Do not leave empty tables blank.

---

# 48. TOAST / NOTIFICATION

Use lightweight notifications.

Examples:

```text
✓ Session berhasil dibuat.
✓ Tugas berhasil dikumpulkan.
✓ Review berhasil disimpan.
✓ Submission ID berhasil disalin.
```

Errors:

```text
Gagal mengunggah file.
Silakan coba lagi.
```

Do not overuse toast notifications.

---

# 49. ACCESSIBILITY

Use:

- Semantic HTML
- Proper labels
- Visible focus states
- Sufficient contrast
- Keyboard-friendly controls
- `aria-label` where necessary
- Do not rely only on color to communicate status

Every input must have a proper label.

---

# 50. PERFORMANCE

Keep the application lightweight.

Avoid:

- Large unnecessary JavaScript libraries
- Heavy animation libraries
- Huge image assets
- Unnecessary API requests
- Unoptimized database queries

Use:

- Laravel eager loading
- Pagination
- Image validation
- Reasonable image dimensions
- Queue for AI processing
- Lazy loading where appropriate

---

# 51. IMAGE HANDLING

Student assignment photos may be large.

Before sending to AI, consider:

- Validate dimensions
- Compress if necessary
- Normalize orientation
- Store original securely
- Generate optimized preview if needed

Do not destroy the original uploaded file.

---

# 52. AI IMAGE LIMIT

Because Arnaru-AI accepts up to 9 files:

Design the AI service to accept:

```text
array<File>
```

rather than hard-coding one file.

MVP may send one image.

Future:

```text
Page 1
Page 2
Page 3
...
```

can be supported.

---

# 53. SOURCE OF TRUTH UI

Admin should have an editor such as:

```text
Source of Truth

Question 1

Question:
[................................]

Expected Answer:
[................................]

Validation Type:
[ Semantic ▼ ]

Required Concepts:
[ concept ]
[ concept ]

----------------------------

[ + Tambah Soal ]
```

For MVP, it is acceptable to store structured data as JSON.

However, design the database and code so it can later be normalized if needed.

---

# 54. SESSION CREATION FLOW

Admin:

```text
Create Session

Title
[ Tugas Informatika ]

Subject
[ Informatika ]

Assignment Date
[ 29 September 2026 ]

Description
[................................]

Source of Truth
[ Questions / answers ]

[ Create Session ]
```

After creation:

```text
Session Created

Session Code:
TSK-INF-290926-X82K

Submission Link:
[ Copy Link ]

[ Open Session ]
```

---

# 55. SESSION CLOSING

When a session is closed:

Students can no longer submit.

Display:

```text
Session Ditutup

Pengumpulan tugas untuk session ini
sudah ditutup.
```

Existing submissions remain accessible.

Admin can still review them.

---

# 56. ADMIN SESSION TABLE

Use columns:

```text
Session
Subject
Date
Submissions
Pending
Status
Actions
```

Do not overcrowd the table.

On mobile, convert rows into cards.

---

# 57. SUBMISSION TABLE

Use:

```text
Student
Class
Submission ID
AI Result
Admin Status
Submitted
Action
```

AI Result and Admin Status must be visually distinct.

Example:

```text
AI:
Likely Completed

Admin:
Pending Review
```

---

# 58. AI RESULT VISUALIZATION

Use an AI badge:

```text
Sparkles icon

AI Recommendation
Likely Completed
```

Confidence:

```text
92% confidence
```

Keep it visually secondary to Admin decision.

Admin decision should be more prominent.

---

# 59. FINAL DECISION UX

Use clear buttons:

```text
[ ✓ Selesai ]
[ × Belum Selesai ]
[ ↻ Perlu Perbaikan ]
```

Before saving, optionally confirm destructive/important decisions.

For example:

```text
Simpan keputusan sebagai
"Selesai"?

[ Batal ] [ Simpan ]
```

---

# 60. ADMIN NOTES

Admin notes can contain:

```text
Nomor 3 belum lengkap.
```

or:

```text
Semua jawaban sudah sesuai.
```

Student-facing visibility should be controlled.

For MVP, Admin Note can be public when the decision is `revision_required`.

Do not expose private/internal notes automatically.

---

# 61. DATA VALIDATION

Server-side validation is mandatory.

Student:

```text
nama_lengkap:
required|string|max:150

kelas:
required|string|max:50

jurusan:
required|string|max:100

catatan:
nullable|string|max:1000

file:
required|image|max:10240
```

Adjust exact validation based on Laravel version and supported MIME formats.

Never rely only on client-side validation.

---

# 62. DUPLICATE SUBMISSIONS

Consider preventing accidental duplicate submissions.

For MVP, do not aggressively block students from submitting again unless explicitly required.

Instead, warn:

```text
Kamu sudah mengirim tugas untuk session ini.
Apakah kamu yakin ingin mengirim lagi?
```

If multiple submissions are allowed, each must receive a unique Submission ID.

---

# 63. DATABASE INDEXES

Add indexes where appropriate:

```text
assignment_sessions.session_code
submissions.submission_code
submissions.session_id
submissions.status
ai_validations.submission_id
admin_reviews.submission_id
```

Make public codes unique.

---

# 64. CONTROLLERS

Keep controllers focused.

Suggested:

```text
AdminDashboardController
AssignmentSessionController
SubmissionController
SubmissionStatusController
AdminSubmissionController
AdminReviewController
```

Do not create one huge controller.

---

# 65. SERVICES

Suggested:

```text
ArnaruAIService
SubmissionService
SubmissionCodeService
SourceOfTruthService
```

Only create services where logic is substantial.

Do not create unnecessary abstraction for trivial CRUD.

---

# 66. JOBS

Required:

```text
ValidateSubmissionWithAI
```

Optional later:

```text
RetryFailedAIValidation
```

---

# 67. FORM REQUESTS

Use Laravel Form Requests for complex validation.

Examples:

```text
StoreAssignmentSessionRequest
UpdateAssignmentSessionRequest
StoreSubmissionRequest
StoreAdminReviewRequest
```

This keeps controllers clean.

---

# 68. BLADE STRUCTURE

Suggested:

```text
resources/views/

layouts/
    app.blade.php
    admin.blade.php

components/
    button.blade.php
    badge.blade.php
    input.blade.php
    textarea.blade.php
    alert.blade.php
    modal.blade.php
    status-badge.blade.php
    upload-box.blade.php

student/
    home.blade.php
    submit.blade.php
    success.blade.php
    check.blade.php
    status.blade.php

admin/
    dashboard.blade.php

    sessions/
        index.blade.php
        create.blade.php
        edit.blade.php
        show.blade.php

    submissions/
        index.blade.php
        show.blade.php
```

Use reusable Blade components where they genuinely improve consistency.

---

# 69. TAILWIND COMPONENT STYLE

Buttons:

Primary:

```text
bg-blue-600
hover:bg-blue-700
text-white
```

Secondary:

```text
bg-white
border
border-slate-200
text-slate-700
```

Danger:

```text
bg-red-600
```

Inputs:

```text
border-slate-200
focus:border-blue-500
focus:ring-blue-500
```

Cards:

```text
bg-white
border
border-slate-200
rounded-xl
```

Do not overuse `shadow-xl`.

---

# 70. LANDING PAGE

Landing page should be simple.

Hero:

```text
Kumpulin!

Kumpulkan tugas dengan mudah.
Pantau statusnya kapan saja.

[ Mulai Kumpulkan ]
[ Cek Pengumpulan ]
```

If the user does not have a session code yet, they can use the session link provided by the teacher.

Do not create a complex marketing landing page for MVP.

---

# 71. NO STUDENT ACCOUNT

This is intentional.

Do NOT implement:

```text
Student Login
Student Register
Password
Forgot Password
Student Dashboard Account
```

Students are identified through:

```text
Nama
Kelas
Jurusan
Submission ID
```

---

# 72. PUBLIC ACCESS SECURITY

Because students do not login, public Submission IDs must be sufficiently unpredictable.

Never use:

```text
SUB-1
SUB-2
SUB-3
```

Use random codes.

Example:

```text
KMP-8F42A91X
```

Rate-limit public status lookup to prevent brute-force enumeration.

---

# 73. RATE LIMITING

Apply rate limiting to:

- Public submission endpoint
- Status checking endpoint
- Admin login

Especially:

```text
/check/{submissionCode}
```

---

# 74. LOGGING

Log important technical events:

```text
Submission created
AI job started
AI job completed
AI job failed
Admin review created
```

Do not log sensitive information unnecessarily.

Do not log API secrets.

---

# 75. AI PROMPT SECURITY

Do not allow students to influence the system prompt.

Student-provided:

```text
Nama
Kelas
Jurusan
Catatan
```

must be treated as untrusted input.

The Source of Truth comes only from the Admin.

The system prompt must come from backend code.

---

# 76. AI RESULT VALIDATION

Never blindly trust the AI's returned JSON.

Backend must validate:

```text
overall_result
confidence
questions
status
```

If malformed:

```text
AI status = error
```

Do not attempt dangerous automatic parsing assumptions.

---

# 77. FINAL STATUS RULE

The final public status must only change after Admin review.

Example:

```text
AI:
likely_completed

Submission:
pending_review

Admin:
completed

Submission:
completed
```

Never:

```text
AI:
likely_completed

Submission:
completed
```

without Admin confirmation.

---

# 78. MVP SCOPE

The first implementation should contain only:

### Student

- Open session
- Fill submission form
- Upload image
- Submit
- Receive Submission ID
- Check status

### Admin

- Login
- Dashboard
- Create session
- Manage Source of Truth
- View submissions
- View AI analysis
- Review submission
- Set final status
- Add note

### AI

- Send image to Arnaru-AI
- Send Source of Truth
- Receive AI result
- Store result
- Retry failed analysis

Do not add unnecessary features before this flow works.

---

# 79. FUTURE FEATURES

Potential future additions:

- Multiple assignment images
- Multiple Source of Truth formats
- Automatic per-question scoring
- Academic grading
- Student notification
- WhatsApp notification
- Email notification
- QR code for session
- QR code for Submission ID
- Export CSV/Excel
- Analytics
- Submission history
- Teacher accounts
- Multiple admin roles
- AI model selection
- AI comparison between models
- Resubmission workflow
- Deadline management

These are NOT required for MVP.

---

# 80. IMPLEMENTATION PRIORITY

Build in this order:

## Phase 1

Laravel setup

```text
Authentication
Tailwind
Layouts
Database
Models
Migrations
```

## Phase 2

Session system

```text
Create Session
Edit Session
Close Session
Source of Truth
```

## Phase 3

Student submission

```text
Public Session
Submission Form
Image Upload
Submission ID
Success Page
```

## Phase 4

Status checking

```text
Check Submission
Status Timeline
Public Result
```

## Phase 5

Arnaru-AI integration

```text
ArnaruAIService
AI Prompt
File Upload
AI Response Parsing
AI Validation Database
```

## Phase 6

Queue

```text
ValidateSubmissionWithAI
Retry AI
Error Handling
```

## Phase 7

Admin Review

```text
Submission List
Submission Detail
AI Analysis
Admin Decision
Admin Notes
```

## Phase 8

Polish

```text
Responsive UI
Empty States
Loading States
Toast
Accessibility
Security
Performance
```

---

# 81. DEFINITION OF DONE

The application is considered functional when this exact scenario works:

```text
Admin logs in
        ↓
Creates:
"Tugas Informatika"
        ↓
Adds Source of Truth
        ↓
System generates Session Code
        ↓
Student opens Session
        ↓
Student fills:
Nama Lengkap
Kelas
Jurusan
Catatan
        ↓
Student uploads assignment photo
        ↓
System creates Submission ID
        ↓
Student receives:
KMP-XXXXXXXX
        ↓
AI Job runs
        ↓
Arnaru-AI analyzes image
        ↓
AI result saved
        ↓
Admin sees submission
        ↓
Admin sees AI analysis
        ↓
Admin checks actual image
        ↓
Admin chooses:
Selesai / Belum Selesai / Perlu Perbaikan
        ↓
Admin saves decision
        ↓
Student checks Submission ID
        ↓
Student sees final status
```

Every step must work without requiring manual database manipulation.

---

# 82. CODE QUALITY RULES

Keep the code:

- Clean
- Readable
- Modular
- Lightweight
- Consistent
- Laravel-conventional

Avoid:

- Giant controllers
- Repeated Blade markup
- Inline API credentials
- Hard-coded URLs
- Hard-coded AI models
- Hard-coded public IDs
- Unnecessary JavaScript
- Unnecessary dependencies
- Over-engineering

Follow Laravel conventions wherever practical.

---

# 83. IMPORTANT IMPLEMENTATION PRINCIPLE

Do not implement the entire project as one huge file or one huge controller.

Separate:

```text
Presentation
Business Logic
AI Integration
Database
Queue
Authentication
```

The architecture should remain easy to maintain.

---

# 84. FINAL PRODUCT PRINCIPLE

Kumpulin! is NOT:

"AI automatically grades students."

Kumpulin! is:

**"A platform that helps students submit assignments and helps administrators validate them using AI-assisted analysis."**

The final authority always remains:

**ADMIN**

The AI only provides:

**RECOMMENDATION + ANALYSIS**

The Source of Truth is provided by:

**ADMIN**

The student interacts mainly with:

**SESSION + SUBMISSION ID + STATUS**

This principle must be preserved throughout the implementation.