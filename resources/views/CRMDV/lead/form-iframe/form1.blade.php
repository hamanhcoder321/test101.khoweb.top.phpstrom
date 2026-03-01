<div class="container">
    <div class="wrapper">
        <form method="get" action="{{ route('form1') }}" class="form-container">
            <input type="text" class="form-field" placeholder="Họ và tên">
            <input type="email" class="form-field" placeholder="Email">
            <input type="tel" class="form-field" placeholder="Số điện thoại">
            <select name="apartment_type[]" id="apartmentType" class="form-select">
                <option value="">Loại căn hộ</option>
                <option value="STUDIO">STUDIO (25 - 31.8m2)</option>
                <option value="CĂN 1PN">CĂN 1PN (23.7 - 33.9m2)</option>
                <option value="CĂN 1PN+1">CĂN 1PN+1 (42.6 - 48.1m2)</option>
                <option value="CĂN 2PN">CĂN 2PN (54.1 - 61.7m2)</option>
                <option value="CĂN 2PN+1">CĂN 2PN+1 (63.7 - 64.7m2)</option>
                <option value="CĂN 3PN">CĂN 3PN (74.1 - 94.1m2)</option>
            </select>
            <button type="submit" class="submit-button">Đăng ký</button>
        </form>
    </div>
</div>

<style>
    /* General form and input styling */
    .container {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .form-container {
        display: flex;
        align-items: center;
<<<<<<< HEAD
        gap: 10px; /* Adjust the gap between elements as needed */
        background-color: #fff; /* Assuming a white background */
        padding: 10px; /* Add padding as needed */
        border-radius: 5px; /* Rounded corners for the form container */
=======
        gap: 10px;
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
>>>>>>> 4f03bf3bbfa462f6abd986c238d8ec617e6e0623
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    select {
<<<<<<< HEAD
        border: 1px solid #ccc; /* Light grey border */
        padding: 10px;
        border-radius: 5px; /* Rounded corners for inputs and select */
        outline: none; /* Removes the default focus outline */
=======
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        outline: none;
>>>>>>> 4f03bf3bbfa462f6abd986c238d8ec617e6e0623
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="tel"]:focus,
    select:focus {
<<<<<<< HEAD
        border-color: #007bff; /* Highlight color when focused */
=======
        border-color: #007bff;
>>>>>>> 4f03bf3bbfa462f6abd986c238d8ec617e6e0623
    }

    /* Button styling */
    button {
<<<<<<< HEAD
        background-color: #007bff; /* Button background color */
        color: #fff; /* Button text color */
        border: none;
        padding: 10px 20px;
        border-radius: 5px; /* Rounded corners for the button */
        cursor: pointer; /* Changes the cursor to a pointer on hover */
    }

    button:hover {
        background-color: #0056b3; /* Darker background color on hover */
=======
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
>>>>>>> 4f03bf3bbfa462f6abd986c238d8ec617e6e0623
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-container {
            flex-direction: column;
        }
    }

</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        function getParameterByName(name, url = window.location.href) {
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        var apartmentType = getParameterByName('apartment_type[]');
        console.log("Apartment Type from URL:", apartmentType); // Check the extracted value

        if (apartmentType) {
            var select = document.getElementById('apartmentType');
            for (var i = 0; i < select.options.length; i++) {
                console.log("Option value:", select.options[i].value); // Check each option value
                if (select.options[i].value === apartmentType) {
                    select.options[i].selected = true;
                    break;
                }
            }
        }
    });

</script>
