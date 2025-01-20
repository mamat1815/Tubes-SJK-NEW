<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats Registration</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/0444f7d0d8.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="style.css">
    <style>
    @keyframes gradientBackground {
        0% { background-position: 0% 50%; }
        25% { background-position: 50% 75%; }
        50% { background-position: 100% 50%; }
        75% { background-position: 50% 25%; }
        100% { background-position: 0% 50%; }
    }

    body {
        background: linear-gradient(-45deg, #4A6D29, #1F4529,rgb(58, 142, 24), #000);
        background-size: 200% 200%;
        animation: gradientBackground 10s ease infinite;
        font-family: 'Arial', sans-serif;
    }

        .container-fluid {
            margin: 50px auto;
            background: #FFF;
            color: #000;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            padding: 30px;
            max-width: 900px;
        }

        .col-md-4 img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 15px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4A6D29;
            font-weight: bold;
        }

        label {
            font-weight: 500;
            color: #4A6D29;
        }

        .form-control:focus {
            border-color: #4A6D29;
            box-shadow: 0 0 5px rgba(74, 109, 41, 0.5);
        }

        .btn-primary {
            background: #4A6D29;
            border: none;
        }

        .btn-primary:hover {
            background: #3a5621;
        }

        #isSellers {
            transition: all 0.3s ease;
        }

        .skill-btn, .add-skill-btn {
            cursor: pointer;
        }

        .add-skill-btn {
            background: #198754;
            color: #fff;
        }

        .add-skill-btn:hover {
            background: #145c3a;
        }

        .skill-btn {
            background: #6c757d;
            color: #fff;
        }

        .skill-btn:hover {
            background: #5a6268;
        }
        .p {
            padding:200 px;
        }
    </style>
</head>
<body>

    <div class="container-fluid p-4 p">
        <div class="row">
            <div class="col-md-12">
                <h2>Register Your Account</h2>
                <form method="POST" action="config/functions/register.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fullname">Fullname</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Insert your fullname" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Insert your full address" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input class="form-control" type="tel" name="phone" id="phone" placeholder="Insert your phone number">
                    </div>

                    <div class="form-group mt-3">
                        <label for="profilePicture">Upload Your Profile Picture</label>
                        <input type="file" class="form-control" id="profilePicture" name="profilePicture">
                    </div>

                    <!-- Checkbox to toggle additional fields -->
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="isSeller" name="isSeller" onclick="toggleAdditionalInfo()">
                        <label class="form-check-label" for="isSeller">Becoming Seller?</label>
                    </div>

                    <!-- Hidden content that appears when checkbox is checked -->
                    <div id="isSellers" style="display: none;">
                        <div class="form-group mt-3">
                            <label for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Write a short bio"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" placeholder="Search and select skills">
                            <div id="skillTags" class="mt-2"></div>
                            <input type="hidden" id="selectedSkills" name="skills">
                        </div>

                        <div class="form-group mt-3">
                            <label for="storePicture">Upload Your Shop's Photo</label>
                            <input type="file" class="form-control" id="storePicture" name="storePicture">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3 w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>    

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        function toggleAdditionalInfo() {
            var checkBox = document.getElementById("isSeller");
            var additionalInfo = document.getElementById("isSellers");
            if (checkBox.checked == true) { 
                additionalInfo.style.display = "block";
            } else {
                additionalInfo.style.display = "none";
            }
        }

        // Fetch Skills with Live Search
        $('#skills').on('input', function () {
            let query = $(this).val().toLowerCase();
            if (query) {
                $.ajax({
                    url: 'config/functions/fetch-skills.php',
                    method: 'POST',
                    data: { search: query },
                    success: function (response) {
                        let skills = JSON.parse(response);
                        $('#skillTags').html('');
                        skills.forEach(skill => {
                            $('#skillTags').append(`<button type="button" class="btn btn-sm skill-btn">${skill}</button>`);
                        });
                        if (!skills.includes(query)) {
                            $('#skillTags').append(`<button type="button" class="btn btn-sm add-skill-btn">+ Add "${query}"</button>`);
                        }
                    }
                });
            }
        });

        // Add Selected Skills
        $('#skillTags').on('click', '.skill-btn', function () {
            let selectedSkill = $(this).text();
            let currentSkills = $('#selectedSkills').val().split(',').filter(skill => skill);
            if (!currentSkills.includes(selectedSkill)) {
                currentSkills.push(selectedSkill);
                $('#selectedSkills').val(currentSkills.join(','));
                $(this).remove();
            }
        });

        // Add New Skill to Database
        $('#skillTags').on('click', '.add-skill-btn', function () {
            let newSkill = $(this).text().replace('+ Add "', '').replace('"', '');
            $.ajax({
                url: 'config/functions/add-skills.php',
                method: 'POST',
                data: { skill: newSkill },
                success: function () {
                    alert(`Skill "${newSkill}" added successfully!`);
                    $('#skills').val('');
                    $('#skillTags').html('');
                }
            });
        });
    </script>
</body>
</html>
