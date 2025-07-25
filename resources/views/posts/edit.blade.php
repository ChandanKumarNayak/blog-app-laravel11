<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            /* Ensures padding doesn't expand width */
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group select {
            appearance: none;
            /* Remove default arrow on some browsers */
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M287%2C197.989L146.205%2C57.194L5.41%2C197.989L0%2C192.579L146.205%2C46.384L292.41%2C192.579L287%2C197.989z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 12px;
        }

        .form-actions {
            text-align: center;
            margin-top: 30px;
        }

        .form-actions button {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .form-actions button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit Post</h1>

        @if (session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p style="color: red">{{ session('error') }}</p>
        @endif

        <form action="{{ route('post.update', $singleRow->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" value="{{ $singleRow->title }}"
                    placeholder="Ex: What is Laravel?">
                @error('title')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description (Full Content)</label>
                <textarea id="description" name="description" placeholder="Ex: Laravel is the most advance PHP Framework...">{{$singleRow->description}}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Post Image</label>
                <img src="{{ asset('storage/'.$singleRow->image) }}" width="50" height="50" alt="">
                <input type="file" id="image" name="image">
                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="inactive" {{ $singleRow->status == 'inactive' ? 'selected' : '' }}>Inactive (Draft)
                    </option>
                    <option value="active" {{ $singleRow->status == 'active' ? 'selected' : '' }}>Active (Published)</option>
                </select>
                @error('status')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit">Update</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ route('home') }}">Back to All Posts</a>
        </div>
    </div>
</body>

</html>
