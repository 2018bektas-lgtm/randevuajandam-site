<?php

use App\Http\Controllers\Api\MobilePatientController;
use App\Http\Controllers\Api\MobileDoctorController;
use App\Http\Controllers\Api\MobileDoctorPortalController;
use App\Http\Controllers\Api\MobileDoctorClinicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobil hasta + hekim uygulaması (React Native) — ana site
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::prefix('doctor')->group(function () {
        Route::post('/auth/login', [MobileDoctorController::class, 'login'])->middleware('throttle:12,1');
        Route::post('/auth/two-factor', [MobileDoctorController::class, 'verifyTwoFactor'])->middleware('throttle:12,1');

        Route::middleware('doktor.mobile')->group(function () {
            // Auth / profile basics
            Route::get('/auth/me', [MobileDoctorController::class, 'me']);
            Route::post('/auth/logout', [MobileDoctorController::class, 'logout']);
            Route::post('/auth/device', [MobileDoctorController::class, 'registerDevice']);
            Route::get('/notifications', [MobileDoctorController::class, 'notifications']);
            Route::post('/notifications/read', [MobileDoctorController::class, 'markNotificationsRead']);
            Route::get('/profile', [MobileDoctorController::class, 'profile']);
            Route::put('/profile', [MobileDoctorController::class, 'updateProfile']);
            Route::get('/meta', [MobileDoctorController::class, 'meta']);
            Route::get('/calendar/ical', [MobileDoctorController::class, 'ical']);
            Route::get('/package-features', [MobileDoctorController::class, 'packageFeatures']);
            Route::get('/packages', [MobileDoctorController::class, 'packages']);
            Route::put('/password', [MobileDoctorPortalController::class, 'updatePassword']);
            Route::get('/about', [MobileDoctorPortalController::class, 'about']);
            Route::put('/about', [MobileDoctorPortalController::class, 'updateAbout']);
            Route::get('/website', [MobileDoctorPortalController::class, 'website']);
            Route::post('/website/setup', [MobileDoctorPortalController::class, 'websiteSetup']);
            Route::post('/website/api-key', [MobileDoctorPortalController::class, 'websiteRegenerateApiKey']);
            Route::put('/website/platform-visibility', [MobileDoctorPortalController::class, 'websitePlatformVisibility']);
            Route::get('/dashboard', [MobileDoctorPortalController::class, 'dashboard']);

            // 2FA
            Route::get('/two-factor', [MobileDoctorPortalController::class, 'twoFactorStatus']);
            Route::post('/two-factor/setup', [MobileDoctorPortalController::class, 'twoFactorBeginSetup']);
            Route::post('/two-factor/confirm', [MobileDoctorPortalController::class, 'twoFactorConfirmSetup']);
            Route::post('/two-factor/disable', [MobileDoctorPortalController::class, 'twoFactorDisable']);
            Route::post('/two-factor/recovery', [MobileDoctorPortalController::class, 'twoFactorRegenerateRecovery']);

            // Clinic membership (basic)
            Route::get('/clinic', [MobileDoctorPortalController::class, 'clinicInfo']);
            Route::get('/clinic/announcements', [MobileDoctorPortalController::class, 'clinicAnnouncements']);
            Route::get('/clinic/patients', [MobileDoctorPortalController::class, 'clinicPatients']);
            Route::post('/clinic/leave', [MobileDoctorPortalController::class, 'clinicLeave']);

            // Clinic admin
            Route::get('/clinic/admin', [MobileDoctorClinicController::class, 'overview']);
            Route::get('/clinic/invites', [MobileDoctorClinicController::class, 'myInvites']);
            Route::post('/clinic/invites/{id}/accept', [MobileDoctorClinicController::class, 'acceptInvite'])->whereNumber('id');
            Route::post('/clinic/invites/{id}/reject', [MobileDoctorClinicController::class, 'rejectInvite'])->whereNumber('id');
            Route::get('/clinic/doctors', [MobileDoctorClinicController::class, 'doctors']);
            Route::post('/clinic/doctors/invite', [MobileDoctorClinicController::class, 'inviteDoctor']);
            Route::delete('/clinic/invites/{id}', [MobileDoctorClinicController::class, 'cancelInvite'])->whereNumber('id');
            Route::post('/clinic/doctors/{id}/remove', [MobileDoctorClinicController::class, 'removeDoctor'])->whereNumber('id');
            Route::post('/clinic/doctors/{id}/toggle', [MobileDoctorClinicController::class, 'toggleDoctorStatus'])->whereNumber('id');
            Route::get('/clinic/staff', [MobileDoctorClinicController::class, 'staff']);
            Route::post('/clinic/staff', [MobileDoctorClinicController::class, 'storeStaff']);
            Route::put('/clinic/staff/{id}', [MobileDoctorClinicController::class, 'updateStaff'])->whereNumber('id');
            Route::post('/clinic/staff/{id}/toggle', [MobileDoctorClinicController::class, 'toggleStaff'])->whereNumber('id');
            Route::delete('/clinic/staff/{id}', [MobileDoctorClinicController::class, 'destroyStaff'])->whereNumber('id');
            Route::get('/clinic/calendar', [MobileDoctorClinicController::class, 'calendar']);
            Route::get('/clinic/requests', [MobileDoctorClinicController::class, 'requests']);
            Route::post('/clinic/requests/bulk-approve', [MobileDoctorClinicController::class, 'bulkApprove']);
            Route::post('/clinic/requests/bulk-reject', [MobileDoctorClinicController::class, 'bulkReject']);
            Route::get('/clinic/expenses', [MobileDoctorClinicController::class, 'expenses']);
            Route::post('/clinic/expenses', [MobileDoctorClinicController::class, 'storeExpense']);
            Route::put('/clinic/expenses/{id}', [MobileDoctorClinicController::class, 'updateExpense'])->whereNumber('id');
            Route::delete('/clinic/expenses/{id}', [MobileDoctorClinicController::class, 'destroyExpense'])->whereNumber('id');

            // Clinic admin — settlements, reports, settings, website, announcements, appointment actions
            Route::get('/clinic/settlements', [MobileDoctorClinicController::class, 'settlements']);
            Route::post('/clinic/settlements', [MobileDoctorClinicController::class, 'calculateSettlement']);
            Route::post('/clinic/settlements/{id}/status', [MobileDoctorClinicController::class, 'updateSettlementStatus'])->whereNumber('id');
            Route::get('/clinic/reports', [MobileDoctorClinicController::class, 'reports']);
            Route::get('/clinic/reports.pdf', [MobileDoctorClinicController::class, 'reportsPdf']);
            Route::get('/clinic/settings', [MobileDoctorClinicController::class, 'settings']);
            Route::put('/clinic/settings', [MobileDoctorClinicController::class, 'updateSettings']);
            Route::get('/clinic/website', [MobileDoctorClinicController::class, 'website']);
            Route::post('/clinic/website/setup', [MobileDoctorClinicController::class, 'websiteSetup']);
            Route::post('/clinic/website/api-key', [MobileDoctorClinicController::class, 'websiteRegenerateApiKey']);
            Route::get('/clinic/announcements/admin', [MobileDoctorClinicController::class, 'adminAnnouncements']);
            Route::post('/clinic/announcements', [MobileDoctorClinicController::class, 'storeAnnouncement']);
            Route::put('/clinic/announcements/{id}', [MobileDoctorClinicController::class, 'updateAnnouncement'])->whereNumber('id');
            Route::post('/clinic/announcements/{id}/toggle', [MobileDoctorClinicController::class, 'toggleAnnouncement'])->whereNumber('id');
            Route::delete('/clinic/announcements/{id}', [MobileDoctorClinicController::class, 'destroyAnnouncement'])->whereNumber('id');
            Route::post('/clinic/appointments/{id}/status', [MobileDoctorClinicController::class, 'updateAppointmentStatus'])->whereNumber('id');
            Route::post('/clinic/appointments/{id}/reschedule', [MobileDoctorClinicController::class, 'rescheduleAppointment'])->whereNumber('id');
            Route::post('/clinic/staff/{id}/reset-password', [MobileDoctorClinicController::class, 'resetStaffPassword'])->whereNumber('id');
            Route::put('/clinic/doctors/{id}', [MobileDoctorClinicController::class, 'updateDoctor'])->whereNumber('id');
            Route::put('/clinic/patients/{id}/note', [MobileDoctorClinicController::class, 'updateClinicPatientNote'])->whereNumber('id');

            // Appointments & calendar
            Route::get('/appointments', [MobileDoctorController::class, 'appointments']);
            Route::get('/calendar', [MobileDoctorController::class, 'calendar']);
            Route::get('/slots', [MobileDoctorController::class, 'daySlots']);
            Route::post('/appointments', [MobileDoctorController::class, 'storeAppointment']);
            Route::get('/appointments/{id}', [MobileDoctorController::class, 'showAppointment'])->whereNumber('id');
            Route::put('/appointments/{id}', [MobileDoctorController::class, 'updateAppointment'])->whereNumber('id');
            Route::delete('/appointments/{id}', [MobileDoctorController::class, 'destroyAppointment'])->whereNumber('id');
            Route::post('/appointments/{id}/reschedule', [MobileDoctorController::class, 'rescheduleAppointment'])->whereNumber('id');
            Route::post('/appointments/{id}/status', [MobileDoctorController::class, 'updateAppointmentStatus'])->whereNumber('id');
            Route::get('/requests', [MobileDoctorPortalController::class, 'requests']);

            // Patients
            Route::get('/patients', [MobileDoctorController::class, 'patients']);
            Route::get('/patients/{id}', [MobileDoctorController::class, 'showPatient'])->whereNumber('id');
            Route::put('/patients/{id}', [MobileDoctorController::class, 'updatePatient'])->whereNumber('id');
            Route::delete('/patients/{id}', [MobileDoctorController::class, 'destroyPatient'])->whereNumber('id');
            Route::post('/patients', [MobileDoctorController::class, 'storePatient']);

            // Services
            Route::get('/services', [MobileDoctorController::class, 'services']);
            Route::post('/services', [MobileDoctorController::class, 'storeService']);
            Route::put('/services/{id}', [MobileDoctorController::class, 'updateService'])->whereNumber('id');
            Route::delete('/services/{id}', [MobileDoctorController::class, 'destroyService'])->whereNumber('id');

            // Settings & working hours & leaves
            Route::get('/appointment-settings', [MobileDoctorController::class, 'appointmentSettings']);
            Route::put('/appointment-settings', [MobileDoctorController::class, 'updateAppointmentSettings']);
            Route::get('/working-hours', [MobileDoctorController::class, 'workingHours']);
            Route::put('/working-hours', [MobileDoctorController::class, 'updateWorkingHours']);
            Route::get('/leaves', [MobileDoctorPortalController::class, 'leaves']);
            Route::post('/leaves', [MobileDoctorPortalController::class, 'storeLeave']);
            Route::delete('/leaves/{id}', [MobileDoctorPortalController::class, 'destroyLeave'])->whereNumber('id');
            Route::get('/quick-close/slots', [MobileDoctorPortalController::class, 'quickCloseSlots']);
            Route::post('/quick-close', [MobileDoctorPortalController::class, 'quickCloseSave']);

            // Waiting list
            Route::get('/waitlist', [MobileDoctorPortalController::class, 'waitlist']);
            Route::post('/waitlist/{id}/status', [MobileDoctorPortalController::class, 'updateWaitlistStatus'])->whereNumber('id');
            Route::post('/waitlist/{id}/notify', [MobileDoctorPortalController::class, 'notifyWaitlist'])->whereNumber('id');
            Route::delete('/waitlist/{id}', [MobileDoctorPortalController::class, 'destroyWaitlist'])->whereNumber('id');

            // Blog
            Route::get('/blogs', [MobileDoctorPortalController::class, 'blogs']);
            Route::post('/blogs', [MobileDoctorPortalController::class, 'storeBlog']);
            Route::put('/blogs/{id}', [MobileDoctorPortalController::class, 'updateBlog'])->whereNumber('id');
            Route::delete('/blogs/{id}', [MobileDoctorPortalController::class, 'destroyBlog'])->whereNumber('id');

            // Reviews
            Route::get('/reviews', [MobileDoctorPortalController::class, 'reviews']);
            Route::post('/reviews/{id}/reply', [MobileDoctorPortalController::class, 'replyReview'])->whereNumber('id');
            Route::post('/reviews/{id}/status', [MobileDoctorPortalController::class, 'moderateReview'])->whereNumber('id');
            Route::delete('/reviews/{id}', [MobileDoctorPortalController::class, 'destroyReview'])->whereNumber('id');

            // Gallery
            Route::get('/gallery', [MobileDoctorPortalController::class, 'gallery']);
            Route::post('/gallery', [MobileDoctorPortalController::class, 'storeGallery']);
            Route::post('/gallery/reorder', [MobileDoctorPortalController::class, 'reorderGallery']);
            Route::put('/gallery/{id}', [MobileDoctorPortalController::class, 'updateGallery'])->whereNumber('id');
            Route::delete('/gallery/{id}', [MobileDoctorPortalController::class, 'destroyGallery'])->whereNumber('id');

            // FAQ
            Route::get('/faqs', [MobileDoctorPortalController::class, 'faqs']);
            Route::post('/faqs', [MobileDoctorPortalController::class, 'storeFaq']);
            Route::put('/faqs/{id}', [MobileDoctorPortalController::class, 'updateFaq'])->whereNumber('id');
            Route::post('/faqs/{id}/toggle', [MobileDoctorPortalController::class, 'toggleFaq'])->whereNumber('id');
            Route::delete('/faqs/{id}', [MobileDoctorPortalController::class, 'destroyFaq'])->whereNumber('id');

            // Education
            Route::get('/educations', [MobileDoctorPortalController::class, 'educations']);
            Route::post('/educations', [MobileDoctorPortalController::class, 'storeEducation']);
            Route::put('/educations/{id}', [MobileDoctorPortalController::class, 'updateEducation'])->whereNumber('id');
            Route::delete('/educations/{id}', [MobileDoctorPortalController::class, 'destroyEducation'])->whereNumber('id');
            Route::get('/educations/{id}/form-fields', [MobileDoctorPortalController::class, 'educationFormFields'])->whereNumber('id');
            Route::put('/educations/{id}/form-fields', [MobileDoctorPortalController::class, 'syncEducationFormFields'])->whereNumber('id');
            Route::get('/education-applications', [MobileDoctorPortalController::class, 'educationApplications']);
            Route::post('/education-applications/{id}/status', [MobileDoctorPortalController::class, 'updateEducationApplication'])->whereNumber('id');
            Route::post('/education-applications/{id}/payment', [MobileDoctorPortalController::class, 'markEducationApplicationPaid'])->whereNumber('id');

            // Finance
            Route::get('/finance/overview', [MobileDoctorPortalController::class, 'financeOverview']);
            Route::get('/finance/report', [MobileDoctorPortalController::class, 'financeReport']);
            Route::get('/finance/report.pdf', [MobileDoctorPortalController::class, 'financeReportPdf']);
            Route::get('/finance/incomes', [MobileDoctorPortalController::class, 'incomes']);
            Route::post('/finance/incomes', [MobileDoctorPortalController::class, 'storeIncome']);
            Route::get('/finance/incomes/{id}', [MobileDoctorPortalController::class, 'showIncome'])->whereNumber('id');
            Route::put('/finance/incomes/{id}', [MobileDoctorPortalController::class, 'updateIncome'])->whereNumber('id');
            Route::post('/finance/incomes/{id}/items', [MobileDoctorPortalController::class, 'storeIncomeItem'])->whereNumber('id');
            Route::delete('/finance/incomes/{odemeId}/items/{kalemId}', [MobileDoctorPortalController::class, 'destroyIncomeItem'])->whereNumber('odemeId')->whereNumber('kalemId');
            Route::delete('/finance/incomes/{id}', [MobileDoctorPortalController::class, 'destroyIncome'])->whereNumber('id');
            Route::get('/finance/expenses', [MobileDoctorPortalController::class, 'expenses']);
            Route::post('/finance/expenses', [MobileDoctorPortalController::class, 'storeExpense']);
            Route::put('/finance/expenses/{id}', [MobileDoctorPortalController::class, 'updateExpense'])->whereNumber('id');
            Route::delete('/finance/expenses/{id}', [MobileDoctorPortalController::class, 'destroyExpense'])->whereNumber('id');
            Route::get('/finance/categories', [MobileDoctorPortalController::class, 'financeCategories']);
            Route::post('/finance/categories', [MobileDoctorPortalController::class, 'storeFinanceCategory']);
            Route::put('/finance/categories/{id}', [MobileDoctorPortalController::class, 'updateFinanceCategory'])->whereNumber('id');
            Route::post('/finance/categories/{id}/toggle', [MobileDoctorPortalController::class, 'toggleFinanceCategory'])->whereNumber('id');
            Route::delete('/finance/categories/{id}', [MobileDoctorPortalController::class, 'destroyFinanceCategory'])->whereNumber('id');
            Route::get('/finance/balances', [MobileDoctorPortalController::class, 'patientBalances']);
        });
    });

    Route::post('/auth/login', [MobilePatientController::class, 'login'])->middleware('throttle:12,1');
    Route::post('/auth/register', [MobilePatientController::class, 'register'])->middleware('throttle:8,1');
    Route::post('/auth/social', [MobilePatientController::class, 'socialLogin'])->middleware('throttle:12,1');

    Route::get('/meta/filters', [MobilePatientController::class, 'filtersMeta'])->middleware('throttle:60,1');

    Route::get('/doctors', [MobilePatientController::class, 'doctors'])->middleware('throttle:60,1');
    Route::get('/doctors/{id}', [MobilePatientController::class, 'doctorShow'])->whereNumber('id')->middleware('throttle:60,1');
    Route::get('/doctors/{id}/slots', [MobilePatientController::class, 'slots'])->whereNumber('id')->middleware('throttle:60,1');

    Route::get('/clinics', [MobilePatientController::class, 'clinics'])->middleware('throttle:60,1');
    Route::get('/clinics/{id}', [MobilePatientController::class, 'clinicShow'])->whereNumber('id')->middleware('throttle:60,1');

    Route::get('/map/pins', [MobilePatientController::class, 'mapPins'])->middleware('throttle:60,1');
    Route::get('/blogs', [MobilePatientController::class, 'blogs'])->middleware('throttle:60,1');
    Route::get('/blogs/{id}', [MobilePatientController::class, 'blogShow'])->whereNumber('id')->middleware('throttle:60,1');
    Route::get('/services', [MobilePatientController::class, 'services'])->middleware('throttle:60,1');
    Route::get('/services/{id}', [MobilePatientController::class, 'serviceShow'])->whereNumber('id')->middleware('throttle:60,1');

    Route::middleware('hasta.mobile')->group(function () {
        Route::get('/auth/me', [MobilePatientController::class, 'me']);
        Route::post('/auth/logout', [MobilePatientController::class, 'logout']);
        Route::put('/auth/profile', [MobilePatientController::class, 'updateProfile']);
        Route::put('/auth/password', [MobilePatientController::class, 'updatePassword']);
        Route::get('/appointments', [MobilePatientController::class, 'myAppointments']);
        Route::post('/appointments', [MobilePatientController::class, 'book'])->middleware('throttle:15,1');
        Route::post('/appointments/{id}/cancel', [MobilePatientController::class, 'cancel'])->whereNumber('id');
    });
});
