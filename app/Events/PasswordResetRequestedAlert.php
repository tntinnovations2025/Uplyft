<?php

namespace App\Events;

/**
 * Password Reset Requested Alert Event (BUG-AUTH-003)
 *
 * Dispatched to render actionable in-app and broadcast alerts
 * on the Principal's dashboard (for Student/Teacher requests)
 * or the Global Admin dashboard (for Principal requests).
 */
class PasswordResetRequestedAlert extends PasswordResetRequested
{
}
