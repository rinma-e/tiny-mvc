<?php

namespace App\Models;

use App\Core\MyPDO;

class UserModel
{
    protected $db;

    public function __construct(MyPDO $db)
    {
        $this->db = $db;
    }

    /**
     * Logs in a user with the given email and password.
     *
     * @param string $email The user's email
     * @param string $password The user's password
     * @return bool|object Returns the user object if login is successful, otherwise returns false
     */
    public function loginUser(string $email, string $password): bool | object
    {
        $sql = "SELECT * FROM users WHERE `email` = :email";

        $result =  $this->db->run($sql, ['email' => $email])->fetch(MyPDO::FETCH_OBJ);

        if ($result) {
            if (password_verify($password, $result->password)) {
                return $result;
            }
            return false;
        }
        return false;
    }

    /**
     * Adds a new user to the database using the provided data.
     *
     * @param array $data the data for the new user
     * @return bool
     */
    public function addUser(array $data): bool
    {
        // the list of allowed field names
        $allowedFieldNames = ["name", "lastname", "email", "password", "roll_id", "avatar"];

        // initialize an string with `fieldname`-s
        $fields = "";

        // initialize an string with :placeholder-s
        $placeholders = "";

        // initialize an array with values:
        $values = [];

        // loop over source data array and form fields, placeholders and values
        foreach ($data as $key => $val) {
            if (in_array($key, $allowedFieldNames)) {
                if ($val) {
                    $fields .= "`$key`,";
                    $placeholders .= ":$key,";
                    $values[$key] = $val;
                }
            }
        }

        $fields = rtrim($fields, ",");
        $placeholders = rtrim($placeholders, ",");
        $sql = "INSERT INTO users ($fields) VALUES ($placeholders)";

        if ($this->db->run($sql, $values)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Update a user with the provided data.
     *
     * @param array $data an array containing user data to be updated
     * @return bool returns true if the user is successfully updated, false otherwise
     */
    public function updateUser(array $data): bool
    {
        // the list of allowed field names
        $allowed = ["name", "lastname", "email", "password", "roll_id"];

        // initialize an array with values:
        $values = [];

        // initialize a string with `fieldname` = :placeholder pairs
        $fieldsString = "";

        // loop over source data array
        foreach ($allowed as $key) {
            if (isset($data[$key]) && $data[$key] !== ""  && $key !== "id") {
                $fieldsString .= "`$key` = :$key,";
                $values[$key] = $data[$key];
            }
        }

        $fieldsString = rtrim($fieldsString, ",");

        $values['id'] = $data['id'];

        $sql = "UPDATE users SET $fieldsString WHERE `id` = :id";

        $result = $this->db->run($sql, $values);

        if ($result->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update user avatar based on the provided data.
     *
     * @param array $data The data containing user avatar information
     * @return bool|string
     */
    public function updateUserAvatar(array $data): bool | string
    {
        $avatar = isset($data['avatar']['name'])
            ? time() . '_avatar_' . $data['id'] . '.' . pathinfo($data['avatar']['name'], PATHINFO_EXTENSION)
            : $data['avatar'];

        $link = '../public/assets/images/avatars/';

        $sql = "UPDATE users SET `avatar` = :avatar WHERE id = :id";

        $result = $this->db->run($sql, ['avatar' => $avatar, 'id' => $data['id']]);

        if ($result->rowCount()) {
            $old_avatar = $_SESSION['user_avatar'];

            if ($avatar) {
                move_uploaded_file($data['avatar']['tmp_name'], $link . $avatar);
            }

            //delete old avatar
            if ($old_avatar && file_exists($link . $old_avatar)) unlink($link . $old_avatar);

            $_SESSION['user_avatar'] = $avatar ?: '';

            // return $data;
            return $avatar ?: true;
        } else {
            return false;
        }
    }


    /**
     * Find user by email.
     *
     * @param string $email The email address of the user
     * @return bool
     */
    public function findUserByEmail(string $email): bool
    {
        $sql = "SELECT count(*) AS user_count FROM users WHERE `email` = :email";

        $result =  $this->db->run($sql, ['email' => $email])->fetch(MyPDO::FETCH_OBJ);

        if ($result->user_count > 0) {
            return true;
        }
        return false;
    }

    /**
     * Find user by password.
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function findUserByPassword(string $email, string $password): bool
    {
        $sql = "SELECT `password` FROM users WHERE `email` = :email";

        $result =  $this->db->run($sql, ['email' => $email])->fetch(MyPDO::FETCH_OBJ);

        if ($result) {
            if (password_verify($password, $result->password)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function passwordResetRequest($email = '')
    {
        if (!empty($email)) {
            //TODO send mail with password reset link
            return true;
        }
        return false;
    }

    //TODO check if password reset link is valid and not expired
    public function passwordResetLinkVerify($link = '')
    {
    }
}
