<?php
/*
 * Helper functions
*/


/**
 * Display the given variable in a human-readable format.
 *
 * @param mixed $x The variable to be displayed
 * @return void
 */
function show($x)
{
    echo '<pre>';
    print_r($x);
    echo '</pre>';
}

/**
 * Display errors in a list format if more than one error is present.
 *
 * @param array $errors The array of errors to display
 */
function show_errors($errors)
{
    if (count($errors) === 1) {
        echo $errors[0];
        return;
    }

    echo '<ul class="text-danger ps-3">';
    foreach ($errors as $error) {
        echo '<li>' . $error . '</li>';
    }
    echo '</ul>';
}

/**
 * Redirects to the specified view.
 *
 * @param string $view The view to redirect to
 */
function redirect($view)
{
    header('Location:' . URL_ROOT . $view);
    die;
}

/**
 * Check if the given array is a multi-dimensional array.
 *
 * @param array $arr The input array to be checked
 * @return bool Returns true if the input is a multi-dimensional array, false otherwise
 */
function isMultiArray($arr)
{
    rsort($arr);
    foreach ($arr as $a) {
        if (!is_array($a)) {
            return false;
        }
    }
    return true;
}

/**
 * Sanitize input string by removing leading/trailing whitespace and converting special characters to HTML entities.
 *
 * @param string $input The input string to be sanitized
 * @return string The sanitized input string
 */
function sanitizeString($input)
{
    return htmlspecialchars(trim($input), ENT_NOQUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8', false);
}

/**
 * Sanitizes the input as an email address.
 *
 * @param string $input The input to be sanitized as an email address
 * @return string The sanitized email address
 */
function sanitizeEmail($input)
{
    return
        filter_var(trim($input), FILTER_SANITIZE_EMAIL);;
}


/**
 * Validates a date to be in format adequate for MySQL.
 *
 * @param string $date The date string to validate
 * @param string $format The format to validate the date against (default is 'Y-m-d H:i:s')
 * @return bool Returns true if the date is valid according to the format, and false otherwise
 */
function validateMySQLDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * Generates a CSRF token.
 *
 * @return string
 */
function csrf_token()
{
    return bin2hex(random_bytes(35));
};

/**
 * Return a human-readable file size
 *
 * @param int $bytes
 * @param int $decimals
 * @return string
 */
function format_filesize(int $bytes, int $decimals = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $units[(int)$factor];
}

/**
 * Resize and save an image file.
 *
 * @param array $file The file to resize and save
 * @param int $width The width of the resized image
 * @param int $height The height of the resized image
 * @throws Exception If the file extension is not supported
 * @return void
 */
function image_resize_and_save(array $file, int $width, int $height): void
{
    $allowed_extensions = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'gif' => 'gif'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // create appropriate function name based on file extension
    $fn_create = 'imagecreatefrom' . $allowed_extensions[$ext];

    // create image from file with appropriate function
    $image = $fn_create($file['tmp_name']);

    // scale image
    $new_image = imagescale($image, $width, $height);

    // create appropriate function name based on file extension
    $fn_image = 'image' . $allowed_extensions[$ext];

    // output image to file with appropriate function
    $fn_image($new_image, $file['tmp_name']);

    // clean up
    imagedestroy($image);
    imagedestroy($new_image);
}

/**
 * Return a mime type of file or false if an error occurred
 *
 * @param string $filename
 * @return string | bool
 */
function get_mime_type(string $filename)
{
    $info = finfo_open(FILEINFO_MIME_TYPE);
    if (!$info) {
        return false;
    }

    $mime_type = finfo_file($info, $filename);
    finfo_close($info);

    return $mime_type;
}
