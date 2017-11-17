<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - Russian (UTF-8)
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
* Translation:  Petrosyan R.
*             for@petrosyan.rv.ua
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.26.2010
*
* Description:  Russian language file for Ion Auth messages and errors
*
*/

// Account Creation
$lang['account_creation_successful'] 	  	 = 'Учетная запись успешно создана';
$lang['account_creation_unsuccessful'] 	 	 = 'Невозможно создать учетную запись';
$lang['account_creation_duplicate_email'] 	 = 'Электронная почта используется или некорректна';
$lang['account_creation_duplicate_username'] 	 = 'Имя пользователя существует или некорректно';

// Password
$lang['password_change_successful'] 	 	 = 'Пароль успешно изменен';
$lang['password_change_unsuccessful'] 	  	 = 'Пароль невозможно изменить';
$lang['forgot_password_successful'] 	 	 = 'Пароль сброшен. На электронную почту отправлено сообщение';
$lang['forgot_password_unsuccessful'] 	 	 = 'Невозможен сброс пароля';
// debug
$lang['forgot_password_unsuccessful_no_email'] 	 	 = 'Невозможен сброс пароля - no email';
$lang['forgot_password_unsuccessful_no_user'] 	 	 = 'Невозможен сброс пароля - no user';
$lang['forgot_password_unsuccessful_no_identity'] 	 	 = 'Невозможен сброс пароля - no identity';

// Activation
$lang['activate_successful'] 		  	 = 'Учетная запись активирована';
$lang['activate_unsuccessful'] 		 	 = 'Не удалось активировать учетную запись';
$lang['deactivate_successful'] 		  	 = 'Учетная запись деактивирована';
$lang['deactivate_unsuccessful'] 	  	 = 'Невозможно деактивировать учетную запись';
$lang['activation_email_successful'] 	  	 = 'Сообщение об активации отправлено';
$lang['activation_email_unsuccessful']   	 = 'Сообщение об активации невозможно отправить';

// Login / Logout
$lang['login_successful'] 		  	 = 'Авторизация прошла успешно';
$lang['login_unsuccessful'] 		  	 = 'Email/пароль не верен';
$lang['logout_successful'] 		 	 = 'Выход успешный';

// Account Changes
$lang['update_successful'] 		 	 = 'Учетная запись успешно обновлена';
$lang['update_unsuccessful'] 		 	 = 'Невозможно обновить учетную запись';
$lang['delete_successful'] 		 	 = 'Учетная запись удалена';
$lang['delete_unsuccessful'] 		 	 = 'Невозможно удалить учетную запись';

// Email Subjects - TODO Please Translate
$lang['email_forgotten_password_subject']    = 'Проверка забытого пароля';
$lang['email_new_password_subject']          = 'Новый пароль';
$lang['email_activation_subject']            = 'Активация учетной записи';

// Forgot Password Email
$lang['email_forgot_password_heading']    = 'Новый пароль для %s';
$lang['email_forgot_password_subheading'] = 'Пожалуйста перейдите по ссылке чтобы %s.';
$lang['email_forgot_password_link']       = 'обновить пароль';

// Reset Password
$lang['reset_password_heading']                               = 'Изменить пароль';
$lang['reset_password_new_password_label']                    = 'Новый пароль (хотя бы %s символов длиной):';
$lang['reset_password_new_password_confirm_label']            = 'Подтвердите новый пароль:';
$lang['reset_password_submit_btn']                            = 'Изменить';
$lang['reset_password_validation_new_password_label']         = 'Новый пароль';
$lang['reset_password_validation_new_password_confirm_label'] = 'Подтвердите новый пароль';