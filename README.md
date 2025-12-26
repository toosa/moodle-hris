# Moodle HRIS Integration Plugin (local_hris)

A comprehensive web service plugin for Moodle that provides REST API endpoints for HRIS (Human Resource Information System) integration.

![Moodle Version](https://img.shields.io/badge/Moodle-4.0%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-green)
![License](https://img.shields.io/badge/License-GPL%20v3-orange)

## 🌟 Features

- **Secure API Access**: API key-based authentication for secure data access
- **Active Course Listing**: Get all visible/active courses with details
- **Participant Management**: Retrieve enrolled participants by course or globally
- **Learning Results**: Comprehensive learning outcomes with pre-test and post-test scores
- **Multi-language Support**: English and Indonesian language packs included
- **REST API Compatible**: Standard Moodle web service architecture

## 🚀 API Endpoints

### 1. Get Active Courses
**Function**: `local_hris_get_active_courses`

Returns list of all visible/active courses in the system.

**Response Fields**:
- `id`: Course ID
- `shortname`: Course short name
- `fullname`: Course full name  
- `summary`: Course description (stripped of HTML)
- `startdate`: Course start timestamp
- `enddate`: Course end timestamp
- `visible`: Course visibility flag

### 2. Get Course Participants
**Function**: `local_hris_get_course_participants`

Get enrolled participants in courses.

**Parameters**:
- `courseid` (optional): Specific course ID (0 for all courses)

**Response Fields**:
- `user_id`: User ID
- `email`: User email address
- `firstname`: User first name
- `lastname`: User last name
- `company_name`: Company/organization name (from user profile)
- `course_id`: Course ID
- `course_shortname`: Course short name
- `course_name`: Course full name
- `enrollment_date`: Enrollment timestamp

### 3. Get Course Results  
**Function**: `local_hris_get_course_results`

Comprehensive learning results with assessment scores.

**Parameters**:
- `courseid` (optional): Specific course ID (0 for all courses)
- `userid` (optional): Specific user ID (0 for all users)

**Response Fields**:
- `user_id`: User ID
- `email`: User email address
- `firstname`: User first name
- `lastname`: User last name
- `company_name`: Company/organization name
- `course_id`: Course ID
- `course_shortname`: Course short name
- `course_name`: Course full name
- `final_grade`: Overall course grade
- `pretest_score`: Pre-test quiz score (detects quiz names containing "pre" and "test")
- `posttest_score`: Post-test quiz score (detects quiz names containing "post" and "test")
- `completion_date`: Course completion timestamp (0 if not completed)
- `is_completed`: Completion status (1 = completed, 0 = not completed)

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

### 5. Create Web Service User & Token
1. Create a dedicated user for API access
2. Go to **Site Administration → Plugins → Web services → Manage tokens**  
3. Create token for the HRIS service and user

## 🔧 API Usage

### Base URL
```
https://yourmoodle.com/webservice/rest/server.php
```

### Authentication
All API calls require:
- `wstoken`: Web service token
- `apikey`: HRIS API key (configured in plugin settings)

### Sample Request (cURL)
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY"
```

### Sample Response
```json
[
  {
    "id": 2,
    "shortname": "course101",
    "fullname": "Introduction to Programming",
    "summary": "Learn basic programming concepts",
    "startdate": 1703980800,
    "enddate": 1706659200,
    "visible": 1
  }
]
```

## 🧪 Testing

Access the built-in API testing interface:
```
https://yourmoodle.com/local/hris/test_api.php
```

This page provides:
- Configuration status check
- Sample API calls
- Setup instructions
- Available function list

## 📋 Requirements

- 🎓 **Moodle**: 4.0+ (tested on Moodle 4.5)
- 🐘 **PHP**: 7.4+
- 🌐 **Web Server**: Apache/Nginx
- 🔧 **Moodle Web Services**: Must be enabled

## 📁 File Structure

```
local/hris/
├── 📄 version.php              # Plugin version and metadata
├── ⚙️ settings.php            # Admin configuration panel
├── 🧪 test_api.php            # API testing interface
├── 🔧 classes/
│   └── external.php           # Web service functions
├── 🗃️ db/
│   └── services.php           # Service definitions
├── 🌐 lang/
│   ├── 🇺🇸 en/
│   │   └── local_hris.php     # English language strings
│   └── 🇮🇩 id/
│       └── local_hris.php     # Indonesian language strings
└── 📖 README.md               # This documentation
```

## 🔒 Security

- API key authentication prevents unauthorized access
- All functions validate the API key before processing
- Uses Moodle's built-in web service security framework
- Respects Moodle's user permissions and context validation

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 🐛 Bug Reports & Feature Requests

Please use the [GitHub Issues](https://github.com/toosa/moodle-hris/issues) page to report bugs or request features.

## 📞 Support

For help and questions:
- 📧 Create an [Issue](https://github.com/toosa/moodle-hris/issues)
- 💬 [Discussions](https://github.com/toosa/moodle-hris/discussions)
- 📖 Check the [Wiki](https://github.com/toosa/moodle-hris/wiki)

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