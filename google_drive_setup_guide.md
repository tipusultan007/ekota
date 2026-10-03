# How to Get Google Drive API Credentials

To connect your Laravel application to Google Drive, you need four specific pieces of information. Here is the step-by-step guide to generating them based on the latest Google Cloud documentation.

## 1. Get `GOOGLE_DRIVE_FOLDER_ID`

This is the easiest step. You need a specific folder in your Google Drive where the backups will be saved.

1. Go to [Google Drive](https://drive.google.com).
2. Create a new folder (e.g., `App Backups`).
3. Double-click to enter the new folder.
4. Look at the URL in your browser's address bar. It will look like this:
   `https://drive.google.com/drive/u/0/folders/1A2b3C4d5E6f7G8h9I0j_K-L`
5. The long string of characters at the end (e.g., `1A2b3C4d5E6f7G8h9I0j_K-L`) is your **Folder ID**.
6. Copy this and paste it as your `GOOGLE_DRIVE_FOLDER_ID`.

---

## 2. Get `GOOGLE_DRIVE_CLIENT_ID` and `GOOGLE_DRIVE_CLIENT_SECRET`

You need to create a project in the Google Cloud Console to get these credentials.

### Step 2.1: Create a Google Cloud Project
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. In the top-left corner, click the **Project Dropdown** (next to the Google Cloud logo) and click **New Project**.
3. Name your project (e.g., `Podda Backups`) and click **Create**.
4. Once created, make sure that project is selected in the top dropdown.

### Step 2.2: Enable the Google Drive API
1. In the left sidebar, go to **APIs & Services** > **Library**.
2. Search for "Google Drive API".
3. Click on **Google Drive API** and click the blue **Enable** button.

### Step 2.3: Configure the OAuth Consent Screen
1. Go to **APIs & Services** > **OAuth consent screen** (in the left sidebar).
2. Choose **External** (unless you have a Google Workspace account and want to restrict it to internal users) and click **Create**.
3. Fill in the required fields:
   - **App name**: E.g., `Laravel Backup`
   - **User support email**: Choose your email address.
   - **Developer contact information**: Choose your email address.
4. Click **Save and Continue** for all the remaining steps (Scopes, Test Users). *Note: If you plan to leave this in "Testing" mode, you must add your own Google Account email under "Test users" on the testing users step.*

### Step 2.4: Create OAuth Credentials
1. Go to **APIs & Services** > **Credentials**.
2. Click the **+ CREATE CREDENTIALS** button at the top and select **OAuth client ID**.
3. For **Application type**, select **Web application**.
4. Under **Authorized redirect URIs**, click **+ ADD URI** and paste exactly this URL:
   `https://developers.google.com/oauthplayground`
   *(This is crucial for the next step to get your refresh token).*
5. Click **Create**.
6. A popup will appear containing your **Client ID** and **Client Secret**. 
7. Copy these and save them as your `GOOGLE_DRIVE_CLIENT_ID` and `GOOGLE_DRIVE_CLIENT_SECRET`.

---

## 3. Get `GOOGLE_DRIVE_REFRESH_TOKEN`

Because your Laravel app needs to upload files automatically in the background without user interaction, you need a Refresh Token that never expires.

1. Go to the [Google OAuth 2.0 Playground](https://developers.google.com/oauthplayground).
2. Click the **Gear icon** (Settings) in the top right corner.
3. Check the box that says **Use your own OAuth credentials**.
4. Paste the **Client ID** and **Client Secret** you just generated into the input fields.
5. Close the settings menu.
6. On the left side (Step 1), scroll down and find **Drive API v3**. Click it to expand.
7. Select the first scope: `https://www.googleapis.com/auth/drive` (which gives full access to Drive).
8. Click the blue **Authorize APIs** button.
9. You will be redirected to log in with your Google account. (You might see a warning saying "Google hasn't verified this app." Click **Advanced** and then **Go to Laravel Backup (unsafe)**).
10. Click **Continue** or **Allow** to grant access.
11. You will be redirected back to the OAuth Playground.
12. On the left side (Step 2), click the blue **Exchange authorization code for tokens** button.
13. The JSON response will appear. Look for the `refresh_token` value.
14. Copy the long string and paste it as your `GOOGLE_DRIVE_REFRESH_TOKEN`.

> [!IMPORTANT]
> Keep your **Refresh Token** and **Client Secret** secure. Never commit them to public version control like GitHub. They act as passwords to your Google Drive.
