<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
    </style>
</head>
<body class="d-flex align-items-center py-4 vh-100">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4 p-sm-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">📦 Gudang 2B</h2>
                            <p class="text-muted">Silakan login untuk melanjutkan</p>
                        </div>
                        
                        @if($errors->any())
                            <div class="alert alert-danger py-2 text-center" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="/login" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted">Alamat Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="email@gudang.com" required autofocus>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted">Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted">
                    <small>&copy; 2026 Sistem Inventaris Kelompok 2B</small>
                </div>

            </div>
        </div>
    </div>

</body>
</html>