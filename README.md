# Active Directory Self-Service Password Reset

A web portal that lets staff reset their own Active Directory password after verifying who they are with a one-time code, so routine password resets no longer need a helpdesk ticket.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-4E56A6?style=flat-square&logo=livewire&logoColor=white)

## How it works

1. The user enters their email address or phone number.
2. The portal searches Active Directory over LDAP for a matching, **enabled** account (by `mail`, `userPrincipalName`, `mobile` or `telephoneNumber`).
3. A six-digit one-time code is sent to the email address or mobile number held in the directory. Codes expire after 10 minutes and allow 5 attempts by default.
4. Once the code is confirmed, the new password is written to the account's `unicodePwd` attribute, so Active Directory's own password policy still applies.

Signed-in users can also view their profile and update their contact details, which keeps the email and phone number used for verification current.

## Features

- Password reset with one-time code verification by email or SMS
- Active Directory sign-in for the self-service dashboard
- Setup wizard for the directory connection on first run
- Separate service accounts for directory lookups and for password resets
- LDAP over SSL or STARTTLS

## Requirements

- PHP 8.3 with the `ldap` extension, Composer, Node.js and npm
- An Active Directory domain reachable from the server
- A service account allowed to read user objects, and an account with the **Reset password** permission on the users that should be able to use the portal
- An encrypted LDAP connection (LDAPS or StartTLS). Active Directory refuses password changes over an unencrypted connection.
- A mail server, and optionally an SMS gateway with an HTTP API

## Getting started

```bash
git clone https://github.com/Alfred336/SSPRs.git
cd SSPRs
composer run setup
composer run dev
```

Then open `/setup` to enter the directory settings, or set them in `.env`:

| Variable | Purpose |
| --- | --- |
| `LDAP_HOST`, `LDAP_PORT` | Domain controller address and port |
| `LDAP_BASE_DN` | Where to search for users |
| `LDAP_USERNAME`, `LDAP_PASSWORD` | Service account for lookups |
| `LDAP_RESET_USERNAME`, `LDAP_RESET_PASSWORD` | Account used for resets (defaults to the lookup account) |
| `LDAP_SSL`, `LDAP_TLS` | Use LDAPS or StartTLS |
| `SSPR_OTP_EXPIRES_MINUTES`, `SSPR_OTP_MAX_ATTEMPTS` | One-time code lifetime and attempt limit |
| `SMS_ENDPOINT`, `SMS_TOKEN`, `SMS_FROM` | SMS gateway settings |
| `SSPR_INSTALLED` | Set to `true` once set-up is complete |

## Security notes

- Serve the portal over HTTPS only.
- Give the reset account the narrowest permissions possible, limited to the organisational units that need self-service.
- Keep `.env` out of version control; it holds directory credentials.

## Author

Alfred Festo — [github.com/Alfred336](https://github.com/Alfred336)
