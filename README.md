# vehicle rental system


##UI designing wire framing and prototyping

link here:      https://www.figma.com/design/0dINaGh7TTPzlSmejcvqwz/Untitled?node-id=0-1&t=l3zf1QL26rHrT99m-1
prototyping link here:       https://www.figma.com/proto/0dINaGh7TTPzlSmejcvqwz/Untitled?node-id=0-1&t=l3zf1QL26rHrT99m-1



#Description:


Hello i made this UI design of our website for our project( vehicle rental system) i have made 
the ui of the home page and the registration page respectively, all the required assets for the
developer is inside the figma link above. If ANY ISSUE You can contact me via Instagram or slack

## AI trip advisor

The dashboard includes an AI advisor that recommends vehicles from the live fleet based on route,
terrain, travelers, days, budget, and notes.

To enable Mistral-powered recommendations, set these environment variables before starting XAMPP:

```bash
export MISTRAL_API_KEY="your_api_key_here"
export MISTRAL_MODEL="mistral-large-latest"
```

If `MISTRAL_API_KEY` is not set, the app still works with the built-in local ranking engine.

## Google login and password reset

Google login is enabled from the login page when these environment variables are set:

```bash
export GOOGLE_CLIENT_ID="your_google_oauth_client_id"
export GOOGLE_CLIENT_SECRET="your_google_oauth_client_secret"
```

Use this redirect URI in Google Cloud:

```text
http://127.0.0.1/Vehicle-Rental-System/api/auth/google_callback.php
```

Forgot password works locally by creating a reset token and showing the reset link on screen. Run
`migrate.php` once if your database was created before these auth fields existed.
