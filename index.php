<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Beranda</title>
  <style>
    :root {
      color-scheme: light;
      font-family: Arial, Helvetica, sans-serif;
      color: #1f2937;
      background: #f7f7f4;
    }

    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      padding: 24px;
    }

    main {
      width: min(100%, 640px);
      text-align: center;
    }

    h1 {
      margin: 0 0 12px;
      font-size: clamp(2rem, 6vw, 4rem);
      font-weight: 700;
      line-height: 1.05;
      color: #111827;
    }

    p {
      margin: 0 auto 28px;
      max-width: 520px;
      font-size: 1rem;
      line-height: 1.7;
      color: #4b5563;
    }

    a {
      display: inline-flex;
      align-items: center;
      min-height: 44px;
      padding: 0 18px;
      border: 1px solid #111827;
      border-radius: 6px;
      color: #111827;
      text-decoration: none;
      font-weight: 600;
      transition: background 160ms ease, color 160ms ease;
    }

    a:hover,
    a:focus-visible {
      background: #111827;
      color: #ffffff;
      outline: none;
    }
  </style>
</head>
<body>
  <main>
    <h1>Selamat Datang</h1>
    <p>Ini adalah halaman awal sederhana untuk website Anda. Ringkas, bersih, dan siap dikembangkan sesuai kebutuhan.</p>
    <a href="#">Mulai</a>
  </main>
</body>
</html>
