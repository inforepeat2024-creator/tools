@php
    $brandColor = $brandColor ?? '#3B82F6';    // Tailwind-ova "blue-500"
    $bgBody     = $bgBody     ?? '#F4F5F7';
    $bgCard     = $bgCard     ?? '#FFFFFF';
    $textColor  = $textColor  ?? '#111827';    // gray-900
    $mutedColor = $mutedColor ?? '#6B7280';    // gray-500
    $title      = $title      ?? '';
    $preheader  = $preheader  ?? '';           // kratki opis koji Gmail prikazuje pored subject-a
@endphp
        <!doctype html>
<html lang="sr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting"> {{-- iOS mail fix --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <!-- Preheader: skriven tekst -->
    <style>
        .preheader { display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; overflow:hidden; mso-hide:all; }
        /* Reset */
        body, table, td, a { -ms-text-size-adjust:100%; -webkit-text-size-adjust:100%; }
        table, td { mso-table-rspace:0pt; mso-table-lspace:0pt; }
        img { -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; }
        table { border-collapse:collapse !important; }
        body { margin:0; padding:0; width:100% !important; background: {{ $bgBody }}; }
        a { text-decoration:none; }

        /* Card & typography */
        .email-container { width:100% !important; }
        .card {
            max-width:600px; margin:0 auto; background: {{ $bgCard }};
            border-radius:12px; overflow:hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .p-24 { padding:24px; }
        .p-32 { padding:32px; }
        .text { color: {{ $textColor }}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
        .muted { color: {{ $mutedColor }}; }
        .h1 { font-size:24px; line-height:1.3; margin:0 0 8px 0; }
        .h2 { font-size:18px; line-height:1.4; margin:0 0 8px 0; }
        .small { font-size:12px; line-height:1.5; }

        /* Button */
        .btn {
            display:inline-block; padding:12px 20px; border-radius:8px;
            background: {{ $brandColor }}; color:#ffffff !important; font-weight:600; font-size:14px;
        }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            .p-32 { padding:20px !important; }
            .h1 { font-size:20px !important; }
            .h2 { font-size:16px !important; }
            .stack-sm { display:block !important; width:100% !important; }
        }

        /* Dark mode hint (ne podržavaju svi klijenti, ali ne škodi) */
        @media (prefers-color-scheme: dark) {
            body { background:#0B0F13 !important; }
            .card { background:#111827 !important; }
            .text { color:#E5E7EB !important; }
            .muted { color:#9CA3AF !important; }
        }
    </style>

    <!--[if mso]>
    <style type="text/css">
        .text { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body>
<!-- Preheader (hidden) -->
<div class="preheader">{{ \Illuminate\Support\Str::limit(strip_tags($preheader), 140) }}</div>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="email-container">
    <tr>
        <td align="center" style="padding:24px;">
            <!-- HEADER -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="card">
                <tr>
                    <td class="p-24 text" style="background: #0F172A; color:#fff;">
                        <table width="100%">
                            <tr>
                                <td align="left">
                                    {{-- Logo / naziv --}}
                                    <span style="font-weight:700; font-size:16px;">{{ $brandName ?? config('app.name') }}</span>
                                </td>
                                <td align="right" class="stack-sm" style="text-align:right;">
                                    @isset($headerRight)
                                        {!! $headerRight !!}
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- HERO / TITLE -->
                <tr>
                    <td class="p-32 text">
                        @hasSection('title')
                            <h1 class="h1">@yield('title')</h1>
                        @elseif(!empty($title))
                            <h1 class="h1">{{ $title }}</h1>
                        @endif
                        @isset($intro)
                            <p class="muted" style="margin:0;">{!! $intro !!}</p>
                        @endisset
                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td class="p-32 text">
                        @yield('content')
                    </td>
                </tr>

                <!-- CALLOUT (optional blok) -->
                @hasSection('callout')
                    <tr>
                        <td class="p-32 text" style="background:#F9FAFB;">
                            @yield('callout')
                        </td>
                    </tr>
                @endif

                <!-- FOOTER -->
                <tr>
                    <td class="p-24 text" style="background:#F3F4F6;">
                        <p class="small muted" style="margin:0 0 8px 0;">
                            @if(!empty($footerText))
                                {!! $footerText !!}
                            @else
                                Primili ste ovu poruku jer ste registrovani na {{ config('app.name') }}.
                            @endif
                        </p>
                        @isset($address)
                            <p class="small muted" style="margin:0;">{{ $address }}</p>
                        @endisset
                        @isset($unsubscribeUrl)
                            <p class="small muted" style="margin:8px 0 0 0;">
                                <a href="{{ $unsubscribeUrl }}" style="color:{{ $brandColor }};">Otkaži pretplatu</a>
                            </p>
                        @endisset
                    </td>
                </tr>
            </table>

            <!-- spacing bottom -->
            <div style="height:16px; line-height:16px;">&zwnj;</div>
        </td>
    </tr>
</table>
</body>
</html>
