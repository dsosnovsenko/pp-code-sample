<?php
namespace Pp\CodeSample;

use Bfc\Core\Filter\AlphaFilter;
use Bfc\Core\Filter\AlphanumericFilter;
use Bfc\Core\Filter\Filter;
use Bfc\Core\Filter\RequestFilter;
use Bfc\Core\Utility\StringUtility;
use Bfc\Core\Validator\AlphanumericValidator;
use Bfc\Core\Validator\EmailValidator;
use Bfc\Core\Validator\RecaptchaValidator;
use Bfc\Core\Validator\RegularExpressionValidator;
use Bfc\Core\Validator\StringLengthValidator;
use Bfc\Core\Validator\TextValidator;
use Bfc\Core\Validator\Validator;
use Pp\CodeSample\Model\User;

class CodeSampleController extends AbstractActionController
{

    /**
     * ReCaptcha data.
     *
     * The siteKey is the required value of HTML attribute 'data-sitekey'.
     * The secretKey is the required value of response 'g-recaptcha-response'.
     *
     * @var array
     */
    protected $reCaptchaData = [
        'code-sample' => [
            'siteKey' => '',
            'secretKey' => '',
        ],
    ];

    /**
     * @var \Pp\CodeSample\Repository\UserRepository
     * @inject
     */
    protected $userRepository = null;

    /**
     * @var \Pp\CodeSample\EmailService
     * @inject
     */
    protected $emailService = null;

    /**
     * @var \Pp\CodeSample\UserAuthentication
     * @inject
     */
    protected $auth = null;

    /**
     * Logged user.
     *
     * @var User
     */
    protected $loggedUser = null;

    /**
     * CodeSampleController constructor.
     */
    public function __construct()
    {
        $this->initializeConfiguration();
    }

    /**
     * Initialize Configuration.
     *
     * @throws \Exception
     */
    protected function initializeConfiguration()
    {
        $pluginConfigFile = __DIR__ . '/../config.php';
        if (!file_exists($pluginConfigFile)) {
            throw new \Exception('Plugin config file "config.php" not found. ' . time());
        }
        // include $pluginConfig from config.php
        /** @var array $pluginConfig */
        include $pluginConfigFile;

        $this->reCaptchaData = $pluginConfig['reCaptchaData'];
    }

    /**
     * Initialize actions.
     *
     * The magic method, called before any output.
     * Use the method for work with header, e.g. session, cookie, etc.
     *
     * @note All action method of controller well be called after output.
     *
     * @return void
     */
    protected function initializeAction()
    {
        // initialize the authentication session
        $this->auth->start();
        $action = $this->getAction();

        switch ($action) {
            case 'loginUser':
                $this->loginUser();
                break;
            case 'logoutUser':
                $this->auth->logout();
                break;
            default:
                // tray auto login by session or stay login cookie
                $this->autoLogin();
                break;
        }
    }

    /**
     * Default method.
     * If action method not found then will be called this method.
     * The magic method.
     */
    protected function defaultAction()
    {
        if ($this->auth->isLogged()) {
            $this->showDashboardAction();
        } else {
            $this->showLoginFormAction();
        }
    }

    /**
     * Show registration and login form.
     *
     * @param array $values
     * @param array $errors
     */
    protected function showLoginFormAction(array $values = [], array $errors = [])
    {
        if ($this->auth->isLogged()) {
            $this->showDashboardAction();

            return;
        }

        $template = $this->getViewTemplate('CodeSample/ShowLoginForm');
        $reCaptchaSiteKey = $this->getRecaptchaSiteKey();
        $template->assign('reCaptchaSiteKey', $reCaptchaSiteKey);
        $template->assign('values', $values);
        $template->assign('errors', $errors);
        $template->render();
    }

    /**
     * Show password recovery form.
     */
    protected function showPasswordRecoveryFormAction()
    {
        $template = $this->getViewTemplate('CodeSample/ShowPasswordRecoveryForm');
        $template->render();
    }

    /**
     * Show dashboard.
     */
    protected function showDashboardAction()
    {
        if (!$this->auth->isLogged()) {
            // Show error message
            $this->showLoginFormAction();

            return;
        }

        $user = $this->getLoggedUser();
        $template = $this->getViewTemplate('CodeSample/ShowDashboard');
        // obviously assign the action, because this action my be called from other action, e.g. loginUserAction
        $template->assign('action', 'showDashboard');
        $template->assign('user', $user);
        $template->render();
    }

    /**
     * Show registration and login form.
     */
    protected function showUpdateUserDataFormAction()
    {
        if (!$this->auth->isLogged()) {
            $this->showLoginFormAction();

            return;
        }

        $user = $this->getLoggedUser();
        $template = $this->getViewTemplate('CodeSample/ShowUpdateUserDataForm');
        $template->assign('action', 'showDashboard');
        $template->assign('user', $user);
        $template->render();
    }

    /**
     * Registration of new user.
     */
    protected function registrationUserAction()
    {
        $values = [];
        $errors = [];

        $objectName = 'registration';
        $fields = [
            'address', 'firstname', 'lastname', 'firm', 'phone', 'email', 'pass', 'pass2', 'accept'
        ];

        foreach ($fields as $field) {
            $values[$objectName . '.' . $field] = $this->getRequestParam($field);
        }

        $reCaptchaSecureKey = $this->getRecaptchaSecretKey();
        /** @var Validator $reCaptchaValidator */
        $reCaptchaValidator = $this->objectManager->get(Validator::class);
        $reCaptchaValidator->addValidator(RecaptchaValidator::class, ['secretKey' => $reCaptchaSecureKey]);

        /** @var \Bfc\Core\Filter\RequestFilter $filter */
        $filter = $this->objectManager->get(RequestFilter::class);
        $charecters = $filter->getCharacters();

        /** @var Validator $nameValidator */
        $nameValidator = $this->objectManager->get(Validator::class);
        $nameValidator->addValidator(StringLengthValidator::class, ['minimum' => 2, 'maximum' => 64]);
        $nameValidator->addValidator(RegularExpressionValidator::class, ['regularExpression' => '{^[' . $charecters . ' \-]+$}su']);

        /** @var Validator $textValidator */
        $textValidator = $this->objectManager->get(Validator::class);
        $textValidator->addValidator(TextValidator::class);

        /** @var Validator $phoneValidator */
        $phoneValidator = $this->objectManager->get(Validator::class);
        $phoneValidator->addValidator(StringLengthValidator::class, ['minimum' => 5, 'maximum' => 32]);
        $phoneValidator->addValidator(RegularExpressionValidator::class, ['regularExpression' => '{^[0-9 \-]+$}su']);

        /** @var Validator $emailValidator */
        $emailValidator = $this->objectManager->get(Validator::class);
        $emailValidator->addValidator(EmailValidator::class);

        /** @var Validator $passwordValidator */
        $passwordValidator = $this->objectManager->get(Validator::class);
        $passwordValidator->addValidator(StringLengthValidator::class, ['minimum' => 5, 'maximum' => 32]);
        $passwordValidator->addValidator(RegularExpressionValidator::class, ['regularExpression' => '{^[0-9a-zA-Z_@#\!\-]+$}su']);

        if (!$nameValidator->validate($values[$objectName . '.firstname'])) {
            $errors[$objectName . '.firstname'] = __('Geben Sie einen gültigen Vornamen ein.');
        }

        if (!$nameValidator->validate($values[$objectName . '.lastname'])) {
            $errors[$objectName . '.lastname'] = __('Geben Sie einen gültigen Nachnamen ein.');
        }

        if (!$values[$objectName . '.address']) {
            $errors[$objectName . '.address'] = __('Wählen Sie eine Anrede aus.');
        }

        if ($reCaptchaValidator->validate() !== true) {
            $errors[$objectName . '.recaptcha'] = __('Ckecken Sie reCaptcha.');
        }

        if (!$textValidator->validate($values[$objectName . '.firm'])) {
            $errors[$objectName . '.firm'] = __('Geben Sie einen gültigen Firmennamen ein.');
        }

        if (!$phoneValidator->validate($values[$objectName . '.phone'])) {
            $errors[$objectName . '.phone'] = __('Geben Sie einen gültigen Telephonenummer ein.');
        }

        if (!$emailValidator->validate($values[$objectName . '.email'])) {
            $errors[$objectName . '.email'] = __('Geben Sie eines gültige E-Mail ein.');
        }

        if (!$passwordValidator->validate($values[$objectName . '.pass'])) {
            $errors[$objectName . '.pass'] = __('Geben Sie einen gültigen Passwort (min 5, max 32 Zeichen) ein.');
        }

        if (!$values[$objectName . '.accept']) {
            $errors[$objectName . '.accept'] = __('Sie sollen die Nutzungsbedingungen akzeptieren.');
        }

        if (count($errors) > 0) {
            $this->showLoginFormAction($values, $errors);

            return;
        }

        $this->emailService->sendRegistration($objectName, $values);
        $template = $this->getViewTemplate('CodeSample/RegistrationSuccess');
        $template->render();
    }

    /**
     * Show dashboard of logged user oder login form by other.
     *
     * This is action method well be called after output.
     */
    protected function loginUserAction()
    {
        if ($this->auth->isLogged()) {
            $this->showDashboardAction();

            return;
        }

        $values = [];
        $errors = [];

        $objectName = 'login';
        $fields = [
            'email', 'pass',
        ];

        foreach ($fields as $field) {
            $values[$objectName . '.' . $field] = $this->getRequestParam($field);
        }

        $errors[$objectName . '.email'] = __('Bitte überprüfen Sie Ihre Zugangsdaten');
        $errors[$objectName . '.pass'] = '';

        $this->showLoginFormAction($values, $errors);
    }

    /**
     * Logout.
     */
    protected function logoutUserAction()
    {
        $template = $this->getViewTemplate('CodeSample/LogoutUser');
        $template->render();
    }

    /**
     * Send email by forgot password.
     */
    protected function forgotPasswordAction()
    {
        $values = [];
        $errors = [];

        $objectName = 'recovery';
        $values[$objectName . '.email'] = $this->getRequestParam('email');

        /** @var Validator $emailValidator */
        $emailValidator = $this->objectManager->get(Validator::class);
        $emailValidator->addValidator(EmailValidator::class);

        if (!$emailValidator->validate($values[$objectName . '.email'])) {
            $errors[$objectName . '.email'] = __('Geben Sie eines gültige E-Mail ein.');
        }

        if (count($errors) > 0) {
            $this->showLoginFormAction($values, $errors);

            return;
        }

        $template = $this->getViewTemplate('CodeSample/PasswordRecoveryExpect');
        $template->render();
    }

    /**
     * Recovery of password.
     */
    protected function recoveryPasswordAction()
    {
        $email = $this->getRequestParam('email');
        $this->emailService->sendPasswordRecovery($email);
        $template = $this->getViewTemplate('CodeSample/PasswordRecoverySuccess');
        $template->render();
    }

    /**
     * Login of user.
     *
     * This method must be called before any output, e.g. in self::initialisationAction()
     *
     * @return bool
     */
    protected function loginUser()
    {
        $email = $this->getRequestParam('email');
        $pass = $this->getRequestParam('pass');
        $stayLogin = (bool)(int) $this->getRequestParam('stay_login');

        $user = $this->userRepository->findUser($email, $pass);

        if ($user instanceof User) {
            // If login success then show dashboard
            $this->auth->login($email, $stayLogin);
            $this->setLoggedUser($user);

            return true;
        }

        return false;
    }

    /**
     * Auto login by cookie if was checked "stay login".
     *
     * @return bool
     */
    protected function autoLogin()
    {
        $loginStay = $this->auth->isStayLogin();
        if (!$loginStay) {
            return false;
        }

        $login = $this->auth->getLoginCookie();

        if ($login) {
            $user = $this->userRepository->findUserByEmail($login);
            if ($user instanceof User) {
                $this->auth->login($user->getEmail(), true);
                $this->setLoggedUser($user);
            }
        }

        return $this->loggedUser
            ? true
            : false;
    }

    /**
     * Get logged user.
     *
     * @return User
     */
    public function getLoggedUser()
    {
        if (!$this->loggedUser) {
            $login = $this->auth->getLoggedUser();
            $this->loggedUser = $this->userRepository->findUserByEmail($login);
        }

        return $this->loggedUser;
    }

    /**
     * Set logged user.
     *
     * @param User $loggedUser
     */
    public function setLoggedUser($loggedUser)
    {
        $this->loggedUser = $loggedUser;
    }

    /**
     * Get ReCaptcha data by current domain.
     *
     * @return array
     */
    protected function getRecaptchaData()
    {
        $domain = $_SERVER['HTTP_HOST'];
        $recaptchaData = [];

        foreach ($this->reCaptchaData as $key => $item) {
            if (strpos($domain, $key) !== false) {
                $recaptchaData = $item;
                break;
            }
        }

        return $recaptchaData;
    }

    /**
     * Get ReCaptcha site key.
     *
     * @return string
     */
    protected function getRecaptchaSiteKey()
    {
        $recaptchaData = $this->getRecaptchaData();

        return isset($recaptchaData['siteKey'])
            ? $recaptchaData['siteKey']
            : '';
    }

    /**
     * Get ReCaptcha secret key.
     *
     * @return string
     */
    protected function getRecaptchaSecretKey()
    {
        $recaptchaData = $this->getRecaptchaData();

        return isset($recaptchaData['secretKey'])
            ? $recaptchaData['secretKey']
            : '';
    }
}