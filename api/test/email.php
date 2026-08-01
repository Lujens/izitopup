<?php
// TEMPORARY TEST FILE — DELETE AFTER TESTING
require_once __DIR__ . '/../../utils/email.php';

$to   = $_GET['to'] ?? '';
if(!$to){ die('Add ?to=youremail@test.com'); }

$sent = sendEmail($to, 'Test User', 'Test Email IziToPop ⚡', emailTemplate("
  <h2 style='color:#0F172A'>Test reyisi! ✅</h2>
  <p style='color:#6B7280'>Si ou resevwa email sa a, Brevo SMTP mache pafètman sou IziToPop.</p>
"));

echo $sent ? 'EMAIL SENT ✅' : 'FAILED ❌ — check SMTP config';
