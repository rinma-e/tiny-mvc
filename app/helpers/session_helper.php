<?php

// start session
session_start();

// flash messages helper
function flashMsg($name = '', $message = ['title' => '', 'body' => ''], $class = null)
{
    if (!empty($name)) {
        if (!empty($message['title'])) {
            if (isset($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class ? $class : 'primary';
        } elseif (empty($message['title']) && isset($_SESSION[$name])) {
            $class = isset($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';

            switch ($class) {
                case 'success':
                    $icon = '<i class="bx bx-check-circle lh-1 me-3 bx-lg"></i>';
                    $close_btn = ' btn-close-white';
                    break;
                case 'danger':
                    $icon = '<i class="bx bx-x-circle lh-1 me-3 bx-lg"></i>';
                    $close_btn = ' btn-close-white';
                    break;
                case 'warning':
                    $icon = '<i class="bx bx-error lh-1 me-3 bx-lg"></i>';
                    $close_btn = '';
                    break;
                case 'info':
                    $icon = '<i class="bx bx-info-circle lh-1 me-3 bx-lg"></i>';
                    $close_btn = '';
                    break;
                case '':
                default:
                    $icon = '';
                    $close_btn = ' btn-close-white';
                    break;
            }
            echo '<div id="flesh-msg" class="alert alert-' . $class . ' text-bg-' . $class . ' alert-dismissible fade show rounded-0 px-0 position-fixed top-0 start-0 end-0 z-1" roll="alert" style="margin-top: 5rem">
                    <div class="d-flex justify-content-center align-items-center mx-auto" style="width: auto; min-width: 320px; max-width: 600px">
                        ' . $icon . '
                        <div class="d-flex flex-column justify-content-center align-items-start text-start">
                            <h4 class="lh-1">' . $_SESSION[$name]['title'] . '</h4>
                            <small class="lh-1">' . $_SESSION[$name]['body'] . '</small>
                        </div>
                        <button type="button" class="btn-close position-relative align-self-start p-0 ms-3' . $close_btn . '" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>';

            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}
