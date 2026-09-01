<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>Tiket {{ $queue->queue_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            color: #000000;
        }

        .ticket {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #000000;
            overflow: hidden;
        }

        .content {
            padding: 30px 24px;
            text-align: center;
        }

        .label {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #000000;
        }

        .queue-number {
            margin: 12px 0 28px;
            font-size: 72px;
            line-height: 1;
            font-weight: bold;
            color: #000000;
        }

        .service-name {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            color: #000000;
        }

        .info {
            margin-top: 22px;
        }

        .info-label {
            margin: 0;
            font-size: 12px;
            color: #000000;
        }

        .info-value {
            margin: 5px 0 0;
            font-size: 15px;
            font-weight: bold;
            color: #000000;
        }

        .footer {
            padding: 18px 24px;
            text-align: center;
            border-top: 1px dashed #000000;
        }

        .footer p {
            margin: 0;
            font-size: 11px;
            line-height: 1.6;
            color: #000000;
        }
    </style>
</head>

<body>

    <div class="ticket">

        <div class="content">

            <p class="label">
                Nomor Antrian
            </p>

            <div class="queue-number">
                {{ $queue->queue_number }}
            </div>

            <p class="service-name">
                {{ $queue->service->name }}
            </p>

            <div class="info">

                <p class="info-label">
                    Diambil pada
                </p>

                <p class="info-value">
                    {{ $queue->created_at
                        ->timezone('Asia/Jakarta')
                        ->format('d F Y, H:i:s') }}
                </p>

            </div>

        </div>

        <div class="footer">

            <p>
                Silakan menunggu hingga nomor Anda dipanggil
                oleh petugas.
            </p>

            <p>
                Harap simpan tiket ini.
            </p>

        </div>

    </div>

</body>
</html>