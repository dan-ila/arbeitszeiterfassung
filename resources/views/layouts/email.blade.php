<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F4F4F4; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
    <tr>
        <td align="center">

            <!-- Main container -->
            <table width="600" cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow: 0 0 10px rgba(0,0,0,0.05);">

                <!-- Header -->
                <tr>
                    <td style="background:#005461; padding:20px; text-align:center;">
                        <h1 style="margin:0; color:#F4F4F4; font-size:22px;">
                            {{ config('app.name') }}
                        </h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:30px; color:#3B4953; font-size:15px; line-height:1.6;">
                        @yield('content')
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#018790; padding:20px; text-align:center;
                               font-size:12px; color:#F4F4F4;">
                        © {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
