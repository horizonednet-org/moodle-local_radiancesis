# RadianceSIS Moodle Integration

The RadianceSIS Moodle Integration plugin provides a seamless bridge between your Moodle site and the Radiance Student Information System. It enables administrative oversight of final grade submissions and provides a robust API for data synchronization.

## Features
- **Compact Grade Reporting**: A dedicated administrative dashboard to track grade submission statuses across all courses.
- **Organization Mapping**: Automated mapping of courses and users to RadianceSIS organizations using category and profile field identifiers.
- **Web Service API**: A comprehensive suite of external functions for syncing users, courses, and grades.
- **Webhook Notifications**: Real-time notifications to RadianceSIS when final grades are submitted.

## Compatibility
- **Moodle Version**: Verified for **Moodle 4.5**.
- **PHP Version**: Compatible with PHP 8.1 and 8.2.

## Installation

1. **Upload the Plugin**:
   Clone or upload the repository into your Moodle installation at:
   `[moodle_root]/local/radiancesis`

2. **Run the Upgrade**:
   Log in to your Moodle site as a Site Administrator and navigate to **Site Administration > Notifications**. Follow the prompts to upgrade the database. 
   Alternatively, run the upgrade via CLI:
   ```bash
   php admin/cli/upgrade.php
   ```

3. **Verify API User**:
   During installation, the plugin automatically creates a dedicated API user:
   - **Name**: Radiance SIS
   - **Username**: `radiancesis`
   - **Email**: `api@radiancesis.com`
   - **Permissions**: This user is automatically added to the Site Administrators list to ensure full capability access for the web service functions.

## API Configuration

To enable RadianceSIS to communicate with your Moodle site, you must provide your RadianceSIS administrator with a Web Service token.

### Generating an API Token
1. Log in as a Site Administrator.
2. Navigate to **Site Administration > Server > Web Services > Manage tokens**.
3. Click **Add**.
4. Select the user **Radiance SIS** (`radiancesis`).
5. Select the service **RadianceSIS Integration**.
6. Click **Save changes**.
7. Copy the generated token and provide it to your RadianceSIS administrator along with your Moodle site URL.

## Configuration Settings
After installation, configure the plugin settings at:
**Site Administration > Plugins > Local plugins > RadianceSIS Integration**

- **Enable Plugin**: Turn the integration on or off globally.
- **Organization Field**: Specify which user profile field contains the RadianceSIS organization slug.
- **Webhook URL**: Enter the endpoint provided by RadianceSIS to receive grade submission notifications.

---
© 2026 Horizon Education Network. Licensed under GPL v3.
