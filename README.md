# Cloudflare Alfred Workflow

Launch Cloudflare zone details in a browser.

![Workflow screenshot](resources/screenshot.png)

Screenshot using the dark variant of my [custom theme](https://github.com/mattstein/alfred-theme).

## Installation

Download the `.alfredworkflow` file from the [latest release](https://github.com/mattstein/alfred-cloudflare-workflow/releases) and double-click to install.

## Configuration

You must provide either a Cloudflare API token (recommended) or a global email address and API key.

| Variable | Default | Required? | Note |
| --- | --- | --- | --- |
| `CLOUDFLARE_API_TOKEN` |  | ✅ | Required and recommended. |
| `CLOUDFLARE_EMAIL` | | ✅ | Required if you don’t create an API token. |
| `CLOUDFLARE_API_KEY` | | ✅ | Required with the email address if you don’t create an API token. |
| `CACHE_SECONDS` | 30 | ❌ | Duration for which cached responses will be re-used. |

### Creating an API Token

The token takes slightly more setup, but it’s safer because you can limit its permissions so you don’t have to stress out if it’s ever compromised. (This workflow uses Cloudflare’s first-party PHP SDK and doesn’t do anything shady, but it’s a Good Idea™ to be very careful about storing secrets.)

1. In the Cloudflare web control panel, click the user avatar in the top right and then click **My Profile**.
2. In the sidebar navigation menu, click **API Tokens**.
3. On this “User API Tokens” page, click **Create Token**.
4. Next to “Create Custom Token” at the bottom of the page, click **Get started**.
5. Fill out the fields.
    - **Token name** is up to you. I picked “Alfred” because this is no time to be clever.
    - **Permissions** should select **Zone**, then **Zone Settings**, and **Read**.
    - **Zone Resources** should select **Include** and **All zones** unless you’d prefer to limit it further.
    - You can skip **Client IP Address Filtering** and **TTL** unless you’d like to set them.
6. Click **Continue to summary**.
7. Confirm by clicking **Create Token**.
8. Copy the resulting token and add it to the workflow’s `CLOUDFLARE_API_TOKEN` environment variable.

That’s it! This way you don’t need your account email address and global key.

## Usage

Use the Alfred trigger `cf` to automatically list your Cloudflare Zones, which can optionally be filtered if you keep typing.

Press <kbd>return</kbd> to open a browser window with that zone in Cloudflare’s control panel.
