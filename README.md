# Moodle HRIS Integration Plugin (local_hris)

A comprehensive web service plugin for Moodle that provides REST API endpoints for HRIS (Human Resource Information System) integration.

![Moodle Version](https://img.shields.io/badge/Moodle-4.0%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-green)
![License](https://img.shields.io/badge/License-GPL%20v3-orange)

## 📚 Documentation

- **[📘 Complete Design Documentation](DESIGN.md)** - Architecture, sequence diagrams, database design
- **[📊 Sequence Diagrams](DIAGRAMS.md)** - Visual flow diagrams in Mermaid format  
- **[⚡ Quick Reference](QUICKREF.md)** - Fast lookup guide for daily use
- **[🔌 API Guide](API_GUIDE.md)** - REST API reference, endpoint docs, request/response examples, and integration guide
- **[🛠️ Installation Guide](#%EF%B8%8F-installation)** - Setup instructions below
- **[🔒 Security Model](#-security)** - Multi-layer security architecture below

## 🌟 Features

- **Secure API Access**: API key-based authentication for secure data access
- **Active Course Listing**: Get all visible/active courses with details
- **Participant Management**: Retrieve enrolled participants by course or globally
- **Learning Results**: Comprehensive learning outcomes with pre-test, post-test, and questionnaire scores
- **Multi-language Support**: English and Indonesian language packs included
- **REST API Compatible**: Standard Moodle web service architecture

> 📖 Lihat **[API_GUIDE.md](API_GUIDE.md)** untuk dokumentasi lengkap endpoint, parameter, contoh request/response, dan integrasi CI3.

## 🛠️ Installation

### Method 1: Download from GitHub

1. Download the latest release from [Releases page](https://github.com/toosa/moodle-hris/releases)
2. Extract and upload the `hris` folder to `/local/` directory in your Moodle installation
3. Visit Site Administration > Notifications to install the plugin
4. Or run: `php admin/cli/upgrade.php --non-interactive`

### Method 2: Git Clone

```bash
cd /path/to/your/moodle/local/
git clone https://github.com/toosa/moodle-hris.git hris
cd hris
php ../../admin/cli/upgrade.php --non-interactive
```

## ⚙️ Configuration

### 1. Enable Web Services
1. Go to **Site Administration → Advanced Features**
2. Enable **Web Services**

### 2. Enable REST Protocol  
1. Go to **Site Administration → Plugins → Web services → Manage protocols**
2. Enable **REST protocol**

### 3. Configure HRIS Plugin
1. Go to **Site Administration → Plugins → Local plugins → HRIS Integration**
2. Enable **HRIS API**
3. Set a secure **API Key** (this will be required for all API calls)

### 4. Create External Service
1. Go to **Site Administration → Plugins → Web services → External services**
2. Add new service or use the pre-installed "HRIS Integration Service"
3. Add these functions:
   - `local_hris_get_active_courses`
   - `local_hris_get_course_participants` 
   - `local_hris_get_course_results`
    - `local_hris_get_all_course_results`

### 5. Create Web Service User & Token
1. Create a dedicated user for API access
2. Go to **Site Administration → Plugins → Web services → Manage tokens**  
3. Create token for the HRIS service and user

## 📋 Requirements

- 🎓 **Moodle**: 4.0+ (tested on Moodle 4.5)
- 🐘 **PHP**: 7.4+
- 🌐 **Web Server**: Apache/Nginx
- 🔧 **Moodle Web Services**: Must be enabled

## 📁 File Structure

```
local/hris/
├── 📄 version.php              # Plugin version and metadata
│                               # - Version number
│                               # - Required Moodle version
│                               # - Dependencies
│
├── ⚙️ settings.php             # Admin configuration panel
│                               # - Enable/disable API toggle
│                               # - API key input field
│                               # - Configuration storage
│
├── 🧪 test_api.php             # API testing interface
│                               # - Connection testing
│                               # - Sample requests
│                               # - Configuration verification
│
├── 🔧 classes/
│   └── external.php            # Web service functions
│                               # - get_active_courses()
│                               # - get_course_participants()
│                               # - get_course_results()
│                               # - get_quiz_score() [private]
│                               # - validate_api_key() [private]
│                               # - Parameter definitions
│                               # - Return value definitions
│
├── 🗃️ db/
│   └── services.php            # Service definitions
│                               # - Function mappings
│                               # - Service configuration
│                               # - Capabilities & permissions
│                               # - AJAX settings
│
├── 🌐 lang/
│   ├── 🇺🇸 en/
│   │   └── local_hris.php      # English language strings
│   │                           # - Plugin name & description
│   │                           # - Setting labels
│   │                           # - Error messages
│   │
│   └── 🇮🇩 id/
│       └── local_hris.php      # Indonesian language strings
│                               # - Terjemahan Bahasa Indonesia
│
├── 📖 README.md                # Plugin overview, installation &
│                               # configuration guide
└── 🔌 API_GUIDE.md             # REST API reference documentation
                                # - Endpoint details & parameters
                                # - Request/response examples
                                # - Integration guide (CI3)
                                # - Architecture & sequence diagrams
```

### Code Structure Explanation

#### external.php Structure
```php
class local_hris_external extends external_api {
    
    // Pattern for each function:
    // 1. {function}_parameters()     - Define input parameters
    // 2. {function}()                - Main function logic
    // 3. {function}_returns()        - Define output structure
    
    // Example:
    public static function get_active_courses_parameters() { }
    public static function get_active_courses($apikey) { }
    public static function get_active_courses_returns() { }
}
```

#### services.php Structure
```php
// Function definitions
$functions = [
    'local_hris_{function_name}' => [
        'classname'   => 'local_hris_external',
        'methodname'  => '{function_name}',
        'classpath'   => 'local/hris/classes/external.php',
        'description' => 'Function description',
        'type'        => 'read',  // or 'write'
        'ajax'        => true,
        'capabilities' => '',
    ]
];

// Service definition
$services = [
    'HRIS Integration Service' => [
        'functions' => [...],
        'enabled' => 1,
        'shortname' => 'hris_service',
    ]
];
```

## 🔒 Security

### Multi-Layer Security Model

#### 1. Transport Layer Security
- **HTTPS Required**: All API communication must use HTTPS
- **SSL/TLS Encryption**: Data encrypted in transit
- **Certificate Validation**: Valid SSL certificate required

#### 2. Web Service Token Authentication
- **Token-Based**: Each request requires valid web service token
- **User Association**: Token linked to specific Moodle user account
- **Permission Control**: Token respects user's capabilities
- **Token Management**: Can be revoked/regenerated anytime

#### 3. Plugin API Key Validation
- **Additional Layer**: Custom API key adds extra security
- **Centralized Storage**: Stored in Moodle config table
- **Per-Request Validation**: Checked on every API call
- **Easy Rotation**: Can be changed without affecting tokens

#### 4. Context & Capability Validation
- **System Context**: All functions validate system context
- **Permission Checks**: Respects Moodle's capability system
- **Data Visibility**: Only returns data user has access to

#### 5. Parameter Validation
- **Type Checking**: Strict parameter type validation (PARAM_INT, PARAM_TEXT, etc)
- **SQL Injection Prevention**: All queries use parameterized statements
- **XSS Protection**: Output properly sanitized
- **Required Fields**: Enforces required parameter validation

### Security Best Practices

1. **Use Strong API Keys**
   - Minimum 32 characters
   - Mix of letters, numbers, and symbols
   - Generate using cryptographically secure methods

2. **Rotate Credentials Regularly**
   - Change API key periodically
   - Regenerate tokens for compromised accounts

3. **Implement IP Whitelisting** (Moodle configuration)
   - Restrict access to known HRIS server IPs
   - Configure at web server level (Apache/Nginx)

4. **Monitor API Usage**
   - Enable Moodle logging
   - Review web service access logs
   - Set up alerts for suspicious activity

5. **Limit Token Permissions**
   - Create dedicated service user
   - Grant minimum necessary capabilities
   - Don't use admin account for API

### API Key Generation Example

```bash
# Generate secure API key (Linux/Mac)
openssl rand -base64 32

# Or using PHP
php -r "echo bin2hex(random_bytes(32));"
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 🐛 Bug Reports & Feature Requests

Please use the [GitHub Issues](https://github.com/toosa/moodle-hris/issues) page to report bugs or request features.

### Reporting Bugs

When reporting a bug, please include:
1. **Moodle Version**: e.g., Moodle 4.5
2. **PHP Version**: e.g., PHP 8.2
3. **Plugin Version**: Check in version.php
4. **Error Message**: Full error message or exception
5. **Steps to Reproduce**: How to trigger the bug
6. **Expected Behavior**: What should happen
7. **Actual Behavior**: What actually happens
8. **Sample Request**: cURL command or API call used

### Requesting Features

When requesting a feature:
1. **Use Case**: Describe your specific need
2. **Expected Behavior**: What should the feature do
3. **Sample Output**: Example of desired response
4. **Priority**: How important is this feature

## 📞 Support

For help and questions:
- 📧 Create an [Issue](https://github.com/toosa/moodle-hris/issues)
- 💬 [Discussions](https://github.com/toosa/moodle-hris/discussions)
- 📖 Check the [Wiki](https://github.com/toosa/moodle-hris/wiki)

### Troubleshooting Common Issues

#### Issue 1: "Invalid API Key" Error
**Solution**: 
1. Check API key in Site Administration → Plugins → Local plugins → HRIS Integration
2. Ensure API key matches exactly (no extra spaces)
3. Verify API is enabled in settings

#### Issue 2: "Access Exception" Error
**Solution**:
1. Check web service token is valid
2. Verify HRIS service is enabled
3. Ensure user has appropriate capabilities
4. Check token hasn't expired

#### Issue 3: Empty Response
**Solution**:
1. Verify courses are visible (not hidden)
2. Check users are actually enrolled
3. Verify database has data to return
4. Check filters (courseid, userid) are correct

#### Issue 4: Pre/Post Test Scores Show 0
**Solution**:
1. Ensure custom field `jenis_quiz` exists on course modules
2. Set `jenis_quiz` value to `2` (PreTest) or `3` (PostTest) on the quiz module
3. Verify grades exist for the quiz (grade items/grades are present)
4. Confirm quizzes are in the correct course

#### Issue 5: Missing Company Name
**Solution**:
1. Create custom profile field with shortname "branch"
2. Go to Site Administration → Users → User profile fields
3. Add new field with shortname exactly: `branch`
4. Users need to fill in this field in their profile

#### Issue 6: Questionnaire Scores Show 0
**Solution**:
1. Ensure a visible questionnaire module exists in the course
2. Ensure the questionnaire has a Rate question (type_id = 8)
3. Confirm users have submitted responses
4. If expecting breakdown scores, ensure the Rate question has exactly 9 choices

## 🔄 Version History

### Version 1.0.0 (2025-01-03)
- ✨ Initial release
- 🎯 Three core API functions
- 🔐 API key authentication
- 📊 Pre/post test score detection
- 🌐 English and Indonesian language support
- 🧪 Built-in testing interface
- 📖 Comprehensive documentation

### Planned Features (Future Versions)

#### Version 1.1.0
- 🔄 Batch user enrollment
- 📧 Email notification support
- 📈 Usage statistics dashboard

#### Version 1.2.0
- 🎓 Certificate download endpoint
- 📝 Custom report generation
- 🔍 Advanced filtering options

#### Version 2.0.0
- 🔌 Webhook support for real-time updates
- 📊 GraphQL API option
- 🔐 OAuth 2.0 authentication

## ⭐ Show Your Support

Give a ⭐️ if this project helped you!

## 📝 License

This project is licensed under the [GNU GPL v3](LICENSE) - see the LICENSE file for details.

## 👨‍💻 Author

**Prihantoosa**
- GitHub: [@toosa](https://github.com/toosa)
- Website: [openstat.toosa.id](https://openstat.toosa.id)

---

<p align="center">Made with ❤️ for HRIS integration with Moodle</p>