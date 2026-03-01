<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Assuming a light grey background */
            width: 100%;
            height: 100vh;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff; /* Assuming a white form background */
            border-radius: 5px; /* Assuming slightly rounded corners on the form */
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); /* Assuming a light shadow around the form */
        }
        input[type="text"], input[type="email"], input[type="tel"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px; /* Assuming slightly rounded corners on the input fields */
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: linear-gradient(to top left, #FFD700 50%, #FFC107 100%);
            border: none;
            color: black;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 3px; /* Assuming slightly rounded corners on the button */
            transition: background-color 0.3s ease; /* Adding a transition for a smooth color change on hover */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
        button:hover {
            background-color: #FFC107; /* Lighter gold color on hover */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
<<<<<<< HEAD
        <form action="#" method="post">
=======
        <form action="{{ route('form2') }}" method="get">
>>>>>>> 4f03bf3bbfa462f6abd986c238d8ec617e6e0623
            <input type="text" id="name" name="name" placeholder="Họ và tên">
            <input type="email" id="email" name="email" placeholder="Email">
            <input type="tel" id="phone" name="phone" placeholder="Số điện thoại">
            <textarea id="message" name="message" placeholder="Lời nhắn"></textarea>
            <button type="submit">Để lại thông tin nhận tư vấn</button>
        </form>
    </div>
</div>

</body>
</html>
