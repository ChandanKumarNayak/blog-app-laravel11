<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Signup Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            background: white;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #0056b3;
        }

        .form-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .form-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="signup-container">
        <h2>Sign Up</h2>

        {{-- flash message display here --}}
        @if (session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p style="color: red">{{ session('error') }}</p>
        @endif
        {{-- flash message display here --}}

        <form action="{{ route('auth.register') }}" method="POST" autocomplete="off">
            @csrf
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Your name"  value="{{old('name')}}" >
                @error('name')
                  <span class="error-message">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" value="{{old('email')}}" autocomplete="off" >
                @error('email')
                  <span class="error-message">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Create Password</label>
                <input type="password" id="password" name="password" placeholder="123456" autocomplete="new-password" >
                @error('password')
                  <span class="error-message">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <input type="password" id="password_confirmation" name="password_confirmation" placeholder="123456" >
              @error('password_confirmation')
                  <span class="error-message">{{$message}}</span>
                @enderror
          </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="{{route('login')}}">Login</a>
        </div>
    </div>

</body>

</html>
