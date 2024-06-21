<?php

class RequestResetPasswordEmailPage extends GTKHTMLPage
{
    public function processPost()
    {
        $email = $_POST['email'];
        
        $formResult = DataAccessManager::get("RequestPasswordResetController")->handleUserRequestPasswordResetLinkForUserID($email);

        if ($formResult->success)
        {
            echo $formResult->message();
            die();
        }
        else
        {
            die($formResult->message());
        }
    }
    public function renderBody()
    {
        ob_start(); ?>

        <h1 class="ml-12 text-2xl font-bold my-4 center">
            Pide un Password Nuevo
        </h1>
        <p class="ml-16 mb-8">
            Se te envia a tu correo. Favor introductir debajo.
        </p>

        <div class="ml-8 w-full max-w-md center">
            <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" placeholder="Email" name="email" required>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
        <?php return ob_get_clean();
    }
}
