# HRIS Plugin - Quick Reference Guide

## 🚀 Quick Start

### Installation (5 minutes)
```bash
cd /path/to/moodle/local/
git clone https://github.com/toosa/moodle-hris.git hris
php ../../admin/cli/upgrade.php --non-interactive
```

### Configuration (5 minutes)
1. Enable Web Services: **Site Admin → Advanced Features → Enable web services** ✅
2. Enable REST: **Site Admin → Plugins → Web services → Manage protocols → REST** ✅
3. Set API Key: **Site Admin → Plugins → Local → HRIS Integration** 🔑
4. Create Token: **Site Admin → Plugins → Web services → Manage tokens** 🎫

---

## 📡 API Quick Reference

### Endpoint
```
POST https://yourmoodle.com/webservice/rest/server.php
```

### Common Parameters
| Parameter | Required | Description |
|-----------|----------|-------------|
| wstoken | ✅ Yes | Web service token |
| wsfunction | ✅ Yes | Function name |
| moodlewsrestformat | ✅ Yes | json or xml |
| apikey | ✅ Yes | Plugin API key |

---

## 🎯 Function Reference

### 1. Get Active Courses

**Function**: `local_hris_get_active_courses`

**Request**:
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY"
```

**Response**:
```json
[{
  "id": 2,
  "shortname": "course101",
  "fullname": "Introduction to Programming",
  "summary": "Learn programming basics",
  "startdate": 1703980800,
  "enddate": 1706659200,
  "visible": 1
}]
```

---

### 2. Get Course Participants

**Function**: `local_hris_get_course_participants`

**Extra Parameters**:
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| courseid | int | No | 0 | Course ID (0 = all) |

**Request**:
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_hris_get_course_participants" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=5"
```

**Response**:
```json
[{
  "user_id": 45,
  "email": "john@company.com",
  "firstname": "John",
  "lastname": "Doe",
  "company_name": "Tech Corp",
  "course_id": 5,
  "course_shortname": "course101",
  "course_name": "Introduction to Programming",
  "enrollment_date": 1704153600
}]
```

---

### 3. Get Course Results

**Function**: `local_hris_get_course_results`

**Extra Parameters**:
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| courseid | int | No | 0 | Course ID (0 = all) |
| userid | int | No | 0 | User ID (0 = all) |

**Request**:
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_hris_get_course_results" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=5" \
  -d "userid=45"
```

**Response**:
```json
[{
  "user_id": 45,
  "email": "john@company.com",
  "firstname": "John",
  "lastname": "Doe",
  "company_name": "Tech Corp",
  "course_id": 5,
  "course_shortname": "course101",
  "course_name": "Introduction to Programming",
  "final_grade": 85.5,
  "pretest_score": 65.0,
  "posttest_score": 90.0,
  "completion_date": 1706659200,
  "is_completed": 1
}]
```

---

## 🔐 Security Checklist

- [ ] HTTPS enabled
- [ ] Strong API key (32+ characters)
- [ ] Web service token created
- [ ] Dedicated service user (not admin)
- [ ] IP whitelisting configured
- [ ] Logging enabled
- [ ] Regular credential rotation

---

## ❌ Common Errors

### Error: Invalid Token
**Cause**: Wrong or expired wstoken  
**Fix**: Regenerate token in Moodle admin

### Error: Invalid API Key
**Cause**: Wrong or missing apikey  
**Fix**: Check plugin settings for correct API key

### Error: Access Exception
**Cause**: User lacks permissions or service disabled  
**Fix**: Enable service and check user capabilities

### Empty Response
**Cause**: No data matching query  
**Fix**: Check filters, verify data exists in Moodle

---

## 🧪 Testing

### Test Endpoint
```
https://yourmoodle.com/local/hris/test_api.php
```

### Quick Test Commands

**Test 1 - Connection**:
```bash
curl -i https://yourmoodle.com/webservice/rest/server.php
```
Expected: 200 OK

**Test 2 - Invalid Token**:
```bash
curl -X POST https://yourmoodle.com/webservice/rest/server.php \
  -d "wstoken=INVALID" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json"
```
Expected: webservice_access_exception

**Test 3 - Invalid API Key**:
```bash
curl -X POST https://yourmoodle.com/webservice/rest/server.php \
  -d "wstoken=YOUR_VALID_TOKEN" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json" \
  -d "apikey=WRONG_KEY"
```
Expected: Invalid API Key error

**Test 4 - Valid Request**:
```bash
curl -X POST https://yourmoodle.com/webservice/rest/server.php \
  -d "wstoken=YOUR_VALID_TOKEN" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY"
```
Expected: JSON array of courses

---

## 📊 Response Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | Success | Request successful, data returned |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Invalid web service token |
| 403 | Forbidden | Invalid API key |
| 500 | Server Error | Internal error |

---

## 🔧 Troubleshooting Flow

```
Issue?
  │
  ├─ Can't access endpoint
  │   └─ Check web services enabled
  │   └─ Check REST protocol enabled
  │   └─ Check HTTPS working
  │
  ├─ "Invalid Token" error
  │   └─ Regenerate web service token
  │   └─ Check token not expired
  │   └─ Verify token in request
  │
  ├─ "Invalid API Key" error
  │   └─ Check plugin settings
  │   └─ Verify API key in request
  │   └─ Check for extra spaces
  │
  ├─ Empty response
  │   └─ Check data exists in Moodle
  │   └─ Verify courses are visible
  │   └─ Check filter parameters
  │
  └─ Pre/Post test scores = 0
      └─ Check quiz name contains "pre/post" AND "test"
      └─ Verify quiz attempts completed
      └─ Check quiz state = 'finished'
```

---

## 📋 Pre-flight Checklist

Before going to production:

**Configuration**:
- [ ] Web services enabled
- [ ] REST protocol enabled
- [ ] HTTPS configured
- [ ] API key set (strong password)
- [ ] Web service created
- [ ] Token generated
- [ ] Service enabled

**Security**:
- [ ] Dedicated service user created
- [ ] Minimal capabilities assigned
- [ ] IP whitelisting configured
- [ ] Logging enabled
- [ ] SSL certificate valid
- [ ] Firewall rules set

**Testing**:
- [ ] Can retrieve courses
- [ ] Can retrieve participants
- [ ] Can retrieve results
- [ ] Pre/post test detection working
- [ ] Error handling correct
- [ ] Performance acceptable

**Documentation**:
- [ ] API key documented (secure location)
- [ ] Token documented (secure location)
- [ ] Endpoint URLs documented
- [ ] Team trained on usage

---

## 🔗 Useful Links

- [Full Documentation](README.md)
- [Design Documentation](DESIGN.md)
- [Sequence Diagrams](DIAGRAMS.md)
- [Moodle Web Services Docs](https://docs.moodle.org/dev/Web_services)
- [Test Interface](https://yourmoodle.com/local/hris/test_api.php)

---

## 💡 Pro Tips

1. **Use descriptive quiz names**: "Pre-Test Module 1" works better than "Test 1"
2. **Create custom profile field**: Add "company" field for organization tracking
3. **Monitor API usage**: Enable logging to track usage patterns
4. **Rotate credentials**: Change API key and tokens regularly
5. **Cache responses**: If data doesn't change often, cache client-side
6. **Batch requests**: If allowed, group requests to reduce API calls
7. **Handle errors gracefully**: Always check response for errors
8. **Use test environment first**: Test thoroughly before production

---

## 📞 Get Help

- 📧 [GitHub Issues](https://github.com/toosa/moodle-hris/issues)
- 💬 [Discussions](https://github.com/toosa/moodle-hris/discussions)
- 📖 [Wiki](https://github.com/toosa/moodle-hris/wiki)
- 🌐 [Author Website](https://openstat.toosa.id)

---

**Version**: 1.0  
**Last Updated**: 2025-01-05  
**Print this page for quick reference!**
