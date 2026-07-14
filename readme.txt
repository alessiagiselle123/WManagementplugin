=== Enterprise Access Control & Admin Protection ===
Contributors: Your Company
Tags: access control, admin protection, security, user management, permissions
Requires at least: 5.0
Requires PHP: 7.2
Tested up to: 6.4
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise-level access control and admin protection plugin for WordPress. Control who sees what in your WordPress admin panel.

== Description ==

Enterprise Access Control & Admin Protection is a powerful WordPress plugin that allows administrators to control which menu items, pages, and features are visible and accessible in the WordPress admin panel.

**Key Features:**

- **Password Protected Settings** - Access the plugin settings with a master password (default: 99999999999999999999)
- **Sidebar Menu Control** - Hide or show any WordPress admin menu and submenu items
- **Toolbar Control** - Control visibility of top admin bar items
- **Dashboard Widgets Control** - Manage which dashboard widgets are visible
- **Admin Pages Protection** - Restrict direct access to hidden pages via URL
- **WooCommerce Integration** - Control WooCommerce menu items
- **Elementor Integration** - Manage Elementor menu visibility
- **Plugin Menus Control** - Hide third-party plugin menus
- **Zero Performance Impact** - Lightweight implementation with no unnecessary overhead
- **Native WordPress Design** - Blends seamlessly with WordPress admin interface
- **Change Master Password** - Update the password from the plugin settings
- **Instant Changes** - Settings apply immediately without page refresh

== Installation ==

1. Upload the plugin folder to your WordPress plugins directory
2. Activate the plugin through the Plugins menu in WordPress
3. A "Manage" menu will appear in the sidebar
4. Click "Manage" to access the settings
5. Enter the default password: 99999999999999999999
6. Configure which menu items and pages should be hidden

== Usage ==

1. **Access Plugin Settings:**
   - Click "Manage" in the WordPress sidebar
   - Enter the master password when prompted
   - You'll see the main control panel

2. **Hide/Show Menu Items:**
   - Go to "Sidebar Menus" tab
   - Check items you want to HIDE
   - Uncheck items you want to SHOW
   - Click "Save Settings"

3. **Change Master Password:**
   - Go to "Security" tab
   - Enter new password
   - Confirm password
   - Click "Update Password"

4. **Default Settings:**
   - All items are visible by default (all checkboxes unchecked)
   - Check a box to hide that item
   - Save to apply changes

== Features ==

**Complete Admin Control:**
- Dashboard
- Posts, Pages, Media
- Comments
- Appearance (Themes, Customize)
- Plugins
- Users
- Tools
- Settings
- WooCommerce (Products, Orders, Coupons, Reports)
- Elementor
- Any third-party plugin menus

**Security Features:**
- Master password protection
- URL access protection for hidden pages
- Nonce verification for all actions
- Proper capability checks
- Input sanitization and escaping

**User Experience:**
- No page refresh required after saving
- Instant visibility changes
- Clean, native WordPress interface
- Bulk actions (Hide All, Show All)
- Organized by category

== Frequently Asked Questions ==

**Q: What is the default password?**
A: The default password is: 99999999999999999999

**Q: Can I change the password?**
A: Yes, go to Security tab and update it whenever you want.

**Q: Do hidden items show in the admin bar?**
A: No, they are completely hidden from all admin locations.

**Q: Can users access hidden pages via direct URL?**
A: No, the plugin blocks direct access with an "Access Denied" message.

**Q: Does this affect my site's performance?**
A: No, the plugin is optimized and has virtually no impact on performance.

**Q: Can hidden items be accessed by other plugins or code?**
A: Users cannot access them through the admin interface.

== Changelog ==

= 1.0.0 =
- Initial release
- Sidebar menu control
- Password protection
- Settings interface
- Security features
- Instant settings application
- Native WordPress design
- Enterprise-level access control

== License ==

This plugin is licensed under the GPL v2 or later.
