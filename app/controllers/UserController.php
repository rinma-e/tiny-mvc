<?php

namespace App\Controllers;

use App\Core\MyPDO;
use App\Core\Controller;
use App\Core\Validation;

class UserController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = $this->loadModel('UserModel', new MyPDO());
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

            // set validation rules
            $fields = [
                'name' => 'required | alphanumeric',
                'lastname' => 'required | alphanumeric',
                'email' => 'required | email',
                'password' => 'required | secure',
                'confirm_password' => 'required | same:password',
                'term_and_cond' => 'accepted',
            ];

            // init data
            $data = [
                'page_title' => 'Register',
                'name' => $_POST['name'],
                'lastname' => $_POST['lastname'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'term_and_cond' => isset($_POST['term_and_cond']) ? 'on' : '',
                'errors' => [],
            ];

            unset($_POST);

            // custom error massages
            $error_messages = [
                'same' => '"Password" and "confirm password" must match',
            ];

            // validate input data
            $data['errors'] = Validation::validate($data, $fields, $error_messages);

            // if there are no errors
            if (empty($data['errors'])) {
                // hash password
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // register user
                $result = $this->userModel->addUser($data);

                if ($result) {
                    flashMsg('registration_success', ['title' => 'Registration successful', 'body' => 'Now You may login.'], 'success');
                    redirect('user/login');
                } else {
                    flashMsg('registration_failed', ['title' => 'Registration failed', 'body' => 'Something went wrong, please try again later.'], 'danger');
                    $data['password'] = $data['confirm_password'];
                    $this->loadView('users/register', $data);
                };
            } else {
                // load view with errors and data
                $this->loadView('users/register', $data);
            }
        } else {
            // if the request method is not POST or csrf token is invalid load empty registration form

            // set csrf token
            $_SESSION['csrf_token'] = csrf_token();

            // init deafult data
            $data = [
                'page_title' => 'Register',
                'name' => '',
                'lastname' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'term_and_cond' => '',
                'errors' => [],
            ];

            $this->loadView('users/register', $data);
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

            // set validation rules
            $fields = [
                'email' => 'required | email',
                'password' => 'required',
            ];

            // init data
            $data = [
                'page_title' => 'Login',
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'remember_me' => isset($_POST['remember_me']) ? 'yes' : '',
                'errors' => [],
            ];

            unset($_POST);

            // validate input data
            $data['errors'] = Validation::validate($data, $fields);

            // if there are no errors
            if (empty($data['errors'])) {
                // login user
                $loggedInUser = $this->userModel->loginUser($data['email'], $data['password']);

                if ($loggedInUser) {
                    // set session variables
                    $this->createUserSession($loggedInUser);
                } else {
                    flashMsg('login_failed', ['title' => 'Login failed', 'body' => 'No user found with supplied credentials.'], 'danger');
                    $this->loadView('users/login', $data);
                }
            } else {
                // load view with errors and data for autofill
                $this->loadView('users/login', $data);
            }
        } else {
            // if the request method is not POST or csrf token is invalid load empty login form

            // set csrf token
            $_SESSION['csrf_token'] = csrf_token();

            // init deafult data
            $data = [
                'page_title' => 'Login',
                'email' => '',
                'password' => '',
                'remember_me' => 'no',
                'errors' => [],
            ];

            $this->loadView('users/login', $data);
        }
    }

    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_lastname']);
        unset($_SESSION['user_roll']);
        unset($_SESSION['user_avatar']);
        session_destroy();
        redirect('user/login');
    }

    public function isLoggedIn()
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        return false;
    }

    private function createUserSession($user)
    {
        $_SESSION['user_id'] = (int) $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_lastname'] = $user->lastname;
        $_SESSION['user_roll'] = $user->roll_id;
        $_SESSION['user_avatar'] = $user->avatar;
        redirect('');
    }

    public function forgot_password()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // init data
            $data = [
                'page_title' => 'Forgot password',
                'email' => $_POST['email'],
                'errors' => [],
            ];

            unset($_POST);

            // validate input data
            $data['errors'] = Validation::validate($data, ['email' => 'required | email']);

            // if there are no errors
            if (empty($data['errors'])) {
                // send email with password reset link
                $sendMail = $this->userModel->passwordResetRequest($data['email']);

                if ($sendMail) {
                    // mail sent successfully
                    flashMsg('mail_sent_success', ['title' => 'Success', 'body' => 'Password reset link successfully sent. Plese check your email.'], 'success');
                    redirect('user/login');
                } else {
                    flashMsg('mail_sent_fail', ['title' => 'Internal error', 'body' => 'Something went wrong, please try again later'], 'danger');
                    $this->loadView('users/forgot_password', $data);
                }
            } else {
                $this->loadView('users/forgot_password', $data);
            }
        } else {
            // if the request method is not POST or csrf token is invalid load empty forgot password form

            // init data
            $data = [
                'page_title' => 'Forgot password',
                'email' => '',
                'errors' => [],
            ];
            $this->loadView('users/forgot_password', $data);
        }
    }

    public function profile()
    {
        if (!$this->isLoggedIn()) {
            redirect('user/login');
        }

        $data = [
            'page_title' => 'Profile',
        ];

        $this->loadView('users/profile', $data);
    }

    public function updateUserProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // set changed flag to false
            $is_data_changed = false;

            // check if data is changed server side
            foreach ($_POST as $key => $value) {
                if (isset($_SESSION[$key]) && $_SESSION[$key] !== $value) {
                    $is_data_changed = true;
                }
            };

            if ($is_data_changed) {
                // init data and set validation rules
                $data['id'] = $_SESSION['user_id'];

                $alowed_fields_with_rules = [
                    'user_name' => 'required | alphanumeric',
                    'user_lastname' => 'required | alphanumeric',
                    'user_email' => 'required | email'
                ];

                // form fields array with validation rules if in POST
                $fields = [];

                // create data and fields arrays based on POST data submitted
                foreach ($_POST as $key => $value) {
                    if (array_key_exists($key, $alowed_fields_with_rules)) {
                        $new_key = str_replace('user_', '', $key);
                        $data[$new_key] = $_POST[$key];
                        $fields[$new_key] = $alowed_fields_with_rules[$key]; // set validation rules
                    }
                }

                unset($_POST);

                // validate input data
                $errors['errors'] = Validation::validate($data, $fields);

                // if there are no errors
                if (empty($errors['errors'])) {
                    // update user profile
                    $result = $this->userModel->updateUser($data);

                    if ($result) {
                        // update session with new data
                        foreach ($_SESSION as $key => $value) {
                            $data_key = str_replace('user_', '', $key);
                            if (isset($data[$data_key])) {
                                $_SESSION[$key] = $data[$data_key];
                            }
                        }
                        // return $result as json
                        return print(json_encode($result, JSON_PRETTY_PRINT));
                    } else {
                        // if false return error message
                        $errors['errors']['update'] = ['Something went wrong. Please try again.'];
                    }
                }
                // return $errors array as json
                return print(json_encode($errors, JSON_PRETTY_PRINT));
            } else {
                // nothing to update so return info message
                $errors['errors']['info'] = ['Nothing to update. No user info has been changed in the form.'];
                return print(json_encode($errors, JSON_PRETTY_PRINT));
            }
        } else {
            // if the request method is not POST or csrf token is invalid send error message
            $errors['errors']['update'] = ['Your request is invalid. Reload page and try again.'];
            return print(json_encode($errors, JSON_PRETTY_PRINT));
        }
    }

    public function checkIsOldPasswordCorrect()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $data = [
                'email' => $_SESSION['user_email'],
                'password' => $_POST['old_password'],
            ];

            unset($_POST);

            $passwordCorrect = $this->userModel->findUserByPassword($data['email'], $data['password']);

            return print(json_encode($passwordCorrect, JSON_PRETTY_PRINT));
        } else {
            // if the request method is not POST or csrf token is invalid send error message
            $errors['errors']['old_password'] = ['Something went wrong. Reload page and try again.'];
            return print(json_encode($errors, JSON_PRETTY_PRINT));
        }
    }

    public function updateUserPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $fields = [
                'password' => 'required | min:8',
                'confirm_password' => 'required | same:password',
            ];

            // init data
            $data = [
                'id' => $_SESSION['user_id'],
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
            ];

            unset($_POST);

            // validate input data
            $errors['errors'] = Validation::validate($data, $fields);

            if (empty($errors['errors'])) {
                unset($data['confirm_password']);

                // hash password
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                $result = $this->userModel->updateUser($data);
                if ($result) {
                    return print(json_encode($result, JSON_PRETTY_PRINT));
                } else {
                    $errors['errors']['update'] = ['Something went wrong. Please try again.'];
                }
            }
            // return $errors array as json
            return print(json_encode($errors, JSON_PRETTY_PRINT));
        } else {
            // if the request method is not POST or csrf token is invalid send error message
            $errors['errors']['update'] = ['Your request is invalid. Reload page and try again.'];
            return print(json_encode($errors, JSON_PRETTY_PRINT));
        }
    }

    public function updateUserAvatar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], json_decode($_POST['csrf_token'], true))) {

            // check if avatar file is uploaded
            $file = isset($_FILES['avatar']);

            if ($file) {
                // for security purposes read file size and type from file itself and set to $_FILES
                $_FILES['avatar']['size'] = filesize($_FILES['avatar']['tmp_name']);
                $_FILES['avatar']['type'] = get_mime_type($_FILES['avatar']['tmp_name']);

                // check if file has an extension and if not try to get it from mime type
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                if (!$ext) {
                    // if file has no extension try to get it from mime type
                    $mime_type = get_mime_type($_FILES['avatar']['tmp_name']);
                    !$mime_type ?: $ext = mb_split("/", $mime_type)[1];

                    // add extension to file name if one is obtained from mime type
                    if ($ext) $_FILES['avatar']['name'] = $_FILES['avatar']['name'] . '.' . $ext;
                }

                // resize image if width or height is greater than 120
                list($width, $height, $type, $attr) = getimagesize($_FILES['avatar']['tmp_name']);
                if ($width > 120 || $height > 120) image_resize_and_save($_FILES['avatar'], 120, 120);
            };

            // set validation rules
            $fields = [
                'avatar' => 'file | max_size:10MB | mimes:jpeg,jpg,png,gif,webp,bmp',
            ];

            // remove post data
            unset($_POST);

            $id = $_SESSION['user_id'];
            $avatar = $file ? $_FILES['avatar'] : null;

            // validate input file and set errors
            $data['errors'] = Validation::validate($_FILES, $fields);

            // if there are no errors
            if (empty($data['errors'])) {

                // update avatar and return new avatar name
                $result = $this->userModel->updateUserAvatar(['id' => $id, 'avatar' => $avatar]);

                if ($result) {
                    is_bool($result) ?: $data['avatar']['new_name'] = $result;
                } else {
                    $data['errors']['upload'] = ['Something went wrong. Please try again.'];
                }
            }
            // return $data array as json
            return print(json_encode($data, JSON_PRETTY_PRINT));
        } else {
            // if the request method is not POST or csrf token is invalid send error message
            $data['errors']['upload'] = ['Your request is invalid. Reload page and try again.'];
            return print(json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}
