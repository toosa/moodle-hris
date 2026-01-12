# HRIS Plugin - Sequence Diagrams

This document contains all sequence diagrams for the HRIS Integration Plugin in Mermaid format.

## Table of Contents
1. [Complete Request-Response Cycle](#1-complete-request-response-cycle)
2. [Get Active Courses Flow](#2-get-active-courses-flow)
3. [Get Course Participants Flow](#3-get-course-participants-flow)
4. [Get Course Results Flow](#4-get-course-results-flow)
5. [Authentication Flow](#5-authentication-flow)
6. [Error Handling Flow](#6-error-handling-flow)

---

## 1. Complete Request-Response Cycle

```mermaid
sequenceDiagram
    participant Client as HRIS Client
    participant Server as Web Server
    participant WS as Moodle Web Service
    participant Token as Token Validator
    participant API as local_hris_external
    participant DB as Moodle Database
    
    Client->>Server: HTTPS POST Request
    Note over Client,Server: Headers + Body with wstoken
    
    Server->>WS: Forward to webservice/rest/server.php
    
    WS->>Token: Validate wstoken
    
    alt Token Invalid
        Token-->>Client: 401 Unauthorized
    else Token Valid
        Token->>WS: Token OK
        WS->>API: Call function with parameters
        
        API->>API: Validate API Key
        
        alt API Key Invalid
            API-->>Client: Error: Invalid API Key
        else API Key Valid
            API->>API: Validate Parameters
            API->>API: Validate Context
            
            API->>DB: Execute Query
            DB-->>API: Raw Data
            
            API->>API: Process & Format Data
            
            API-->>WS: Return Array
            WS-->>Server: JSON Response
            Server-->>Client: HTTPS Response
        end
    end
```

---

## 2. Get Active Courses Flow

```mermaid
sequenceDiagram
    participant HRIS as HRIS System
    participant WS as Moodle Web Service
    participant API as local_hris_external
    participant DB as Moodle Database
    
    HRIS->>WS: POST /webservice/rest/server.php
    Note over HRIS,WS: wstoken + apikey + wsfunction
    
    WS->>API: local_hris_get_active_courses(apikey)
    
    API->>API: validate_parameters(apikey)
    API->>API: validate_api_key(apikey)
    
    alt API Key Invalid
        API-->>HRIS: Error: Invalid API Key
    else API Key Valid
        API->>API: validate_context(system)
        API->>DB: SELECT courses WHERE visible=1
        DB-->>API: Course records
        
        loop For each course
            API->>API: Format course data
        end
        
        API-->>WS: Array of courses
        WS-->>HRIS: JSON Response
    end
```

### Detailed Flow with Validation

```mermaid
sequenceDiagram
    participant Client
    participant API as local_hris_external
    participant Validator
    participant DB as Database
    
    Client->>API: get_active_courses(apikey)
    
    API->>Validator: validate_parameters()
    Validator-->>API: params validated
    
    API->>Validator: validate_api_key(apikey)
    
    alt Invalid Key
        Validator-->>Client: Exception: invalidapikey
    else Valid Key
        Validator-->>API: key OK
        
        API->>Validator: validate_context(system)
        Validator-->>API: context OK
        
        Note over API,DB: SQL Query
        API->>DB: SELECT id, shortname, fullname, summary,<br/>startdate, enddate, visible<br/>FROM mdl_course<br/>WHERE id != 1 AND visible = 1<br/>ORDER BY fullname
        
        DB-->>API: ResultSet
        
        loop For each course
            API->>API: Format course data<br/>- Remove HTML from summary<br/>- Ensure all fields present
        end
        
        API-->>Client: Array of courses (JSON)
    end
```

---

## 3. Get Course Participants Flow

```mermaid
sequenceDiagram
    participant HRIS as HRIS System
    participant WS as Moodle Web Service
    participant API as local_hris_external
    participant DB as Moodle Database
    
    HRIS->>WS: POST /webservice/rest/server.php
    Note over HRIS,WS: wstoken + apikey + wsfunction + courseid
    
    WS->>API: get_course_participants(apikey, courseid)
    
    API->>API: validate_parameters()
    API->>API: validate_api_key(apikey)
    
    alt API Key Invalid
        API-->>HRIS: Error: Invalid API Key
    else API Key Valid
        API->>API: validate_context(system)
        
        alt courseid > 0
            API->>DB: SELECT users WHERE course_id=courseid
        else courseid = 0
            API->>DB: SELECT users FROM all courses
        end
        
        DB-->>API: Enrollment records with user info
        
        loop For each participant
            API->>API: Format participant data
        end
        
        API-->>WS: Array of participants
        WS-->>HRIS: JSON Response
    end
```

### Detailed Flow with Filtering

```mermaid
sequenceDiagram
    participant Client
    participant API as local_hris_external
    participant DB as Database
    
    Client->>API: get_course_participants(apikey, courseid)
    
    API->>API: Validate parameters & API key
    
    alt courseid = 0
        Note over API: Get all participants from all courses
        API->>DB: SELECT users FROM all courses<br/>JOIN user_enrolments<br/>JOIN enrol<br/>JOIN course<br/>LEFT JOIN user_info_data (company)
    else courseid > 0
        Note over API: Get participants for specific course
        API->>DB: SELECT users WHERE course_id = courseid<br/>JOIN user_enrolments<br/>JOIN enrol<br/>JOIN course<br/>LEFT JOIN user_info_data (company)
    end
    
    DB-->>API: Participant records
    
    loop For each participant
        API->>API: Format data:<br/>- user_id<br/>- email<br/>- firstname, lastname<br/>- company_name<br/>- course info<br/>- enrollment_date
    end
    
    API-->>Client: Array of participants (JSON)
```

---

## 4. Get Course Results Flow

```mermaid
sequenceDiagram
    participant HRIS as HRIS System
    participant WS as Moodle Web Service
    participant API as local_hris_external
    participant DB as Moodle Database
    
    HRIS->>WS: POST /webservice/rest/server.php
    Note over HRIS,WS: wstoken + apikey + wsfunction + courseid + userid
    
    WS->>API: get_course_results(apikey, courseid, userid)
    
    API->>API: validate_parameters()
    API->>API: validate_api_key(apikey)
    
    alt API Key Invalid
        API-->>HRIS: Error: Invalid API Key
    else API Key Valid
        API->>API: validate_context(system)
        
        alt Filters applied
            API->>DB: SELECT with courseid/userid filters
        else No filters
            API->>DB: SELECT all results
        end
        
        DB-->>API: Enrollment & grade records
        
        loop For each enrollment
            API->>DB: get_quiz_score(userid, courseid, 'pre')
            DB-->>API: Pre-test score
            
            API->>DB: get_quiz_score(userid, courseid, 'post')
            DB-->>API: Post-test score
            
            API->>API: Format result data
        end
        
        API-->>WS: Array of results
        WS-->>HRIS: JSON Response
    end
```

### Detailed Flow with Score Calculation

```mermaid
sequenceDiagram
    participant Client
    participant API as local_hris_external
    participant DB as Database
    
    Client->>API: get_course_results(apikey, courseid, userid)
    
    API->>API: Validate parameters & API key
    
    API->>DB: SELECT users with enrollments,<br/>completions, grades<br/>WHERE conditions based on filters
    
    DB-->>API: Enrollment records
    
    loop For each enrollment
        Note over API,DB: Get pre-test score
        API->>DB: SELECT MAX(sumgrades)<br/>FROM quiz_attempts<br/>WHERE quiz name ILIKE '%pre%test%'<br/>AND state = 'finished'
        DB-->>API: pre_score
        
        Note over API,DB: Get post-test score
        API->>DB: SELECT MAX(sumgrades)<br/>FROM quiz_attempts<br/>WHERE quiz name ILIKE '%post%test%'<br/>AND state = 'finished'
        DB-->>API: post_score
        
        API->>API: Build result object:<br/>- user info<br/>- course info<br/>- final_grade<br/>- pretest_score<br/>- posttest_score<br/>- completion_date<br/>- is_completed
    end
    
    API-->>Client: Array of results (JSON)
```

---

## 5. Authentication Flow

```mermaid
sequenceDiagram
    participant Client as External Client
    participant WS as Moodle Web Service
    participant Auth as Token Validation
    participant API as local_hris_external
    participant Config as Plugin Config
    
    Client->>WS: Request with wstoken
    WS->>Auth: Validate web service token
    
    alt Token Invalid
        Auth-->>Client: Error: Invalid Token
    else Token Valid
        Auth->>API: Call web service function
        API->>API: Extract apikey parameter
        API->>Config: get_config('local_hris', 'api_key')
        Config-->>API: Stored API key
        
        alt API Key Mismatch
            API-->>Client: Error: Invalid API Key
        else API Key Match
            API->>API: Process request
            API-->>Client: Success Response
        end
    end
```

### Detailed Authentication with User Context

```mermaid
sequenceDiagram
    participant Client
    participant WS as Web Service
    participant TokenDB as Token Storage
    participant API as local_hris
    participant ConfigDB as Config Storage
    
    Client->>WS: Request + wstoken
    
    WS->>TokenDB: Validate token
    TokenDB-->>WS: Token details (user, service, validity)
    
    alt Token Invalid/Expired
        WS-->>Client: 401 Unauthorized
    else Token Valid
        WS->>WS: Check service enabled
        WS->>WS: Check user has capability
        
        WS->>API: Call function + apikey
        
        API->>ConfigDB: get_config('local_hris', 'api_key')
        ConfigDB-->>API: stored_key
        
        API->>API: Compare apikey === stored_key
        
        alt API Key Mismatch
            API-->>Client: Exception: Invalid API Key
        else API Key Match
            API->>API: Execute function
            API-->>Client: Success response
        end
    end
```

---

## 6. Error Handling Flow

```mermaid
sequenceDiagram
    participant Client
    participant WS as Web Service
    participant API as local_hris
    
    Client->>WS: Request with invalid token
    WS->>WS: Validate token
    WS-->>Client: webservice_access_exception
    
    Client->>WS: Request with valid token
    WS->>API: Call function
    API->>API: Validate API key
    API-->>Client: moodle_exception: invalidapikey
    
    Client->>WS: Request with missing parameters
    WS->>API: Call function
    API->>API: validate_parameters()
    API-->>Client: invalid_parameter_exception
    
    Client->>WS: Valid request but no data
    WS->>API: Call function
    API->>API: Execute query
    API-->>Client: Empty array []
```

### Detailed Error Scenarios

```mermaid
sequenceDiagram
    participant Client
    participant WS as Web Service
    participant API as local_hris_external
    participant DB as Database
    
    Note over Client,DB: Scenario 1: Invalid Token
    Client->>WS: POST with invalid wstoken
    WS->>WS: validate_token()
    WS-->>Client: 401 Unauthorized<br/>webservice_access_exception
    
    Note over Client,DB: Scenario 2: Invalid API Key
    Client->>WS: POST with valid wstoken, invalid apikey
    WS->>API: Call function
    API->>API: validate_api_key(apikey)
    API-->>Client: 403 Forbidden<br/>moodle_exception: invalidapikey
    
    Note over Client,DB: Scenario 3: Missing Required Parameter
    Client->>WS: POST without required parameter
    WS->>API: Call function
    API->>API: validate_parameters()
    API-->>Client: 400 Bad Request<br/>invalid_parameter_exception
    
    Note over Client,DB: Scenario 4: Database Error
    Client->>WS: POST with valid credentials
    WS->>API: Call function
    API->>DB: Execute query
    DB-->>API: SQL Error
    API-->>Client: 500 Internal Server Error<br/>dml_exception
    
    Note over Client,DB: Scenario 5: No Data Found
    Client->>WS: POST with valid credentials
    WS->>API: Call function
    API->>DB: Execute query
    DB-->>API: Empty ResultSet
    API->>API: Process results
    API-->>Client: 200 OK<br/>[] (empty array)
```

---

## Usage Instructions

### Viewing Diagrams

These diagrams use Mermaid syntax. To view them:

1. **GitHub**: GitHub automatically renders Mermaid diagrams in markdown files
2. **VS Code**: Install the "Markdown Preview Mermaid Support" extension
3. **Online**: Copy to [Mermaid Live Editor](https://mermaid.live/)
4. **Documentation Sites**: Use MkDocs with mermaid2 plugin

### Editing Diagrams

To modify these diagrams:

1. Use the Mermaid syntax reference: https://mermaid.js.org/
2. Test changes in the Mermaid Live Editor
3. Common elements:
   - `participant`: Define an actor in the sequence
   - `->`: Solid arrow (synchronous call)
   - `-->>`: Dashed arrow (return/response)
   - `Note over`: Add notes above actors
   - `alt/else/end`: Conditional logic
   - `loop/end`: Repetitive logic

### Exporting Diagrams

To export as images:

1. Use Mermaid CLI: `mmdc -i DIAGRAMS.md -o diagram.png`
2. Or use the Mermaid Live Editor's export function
3. Or use VS Code with Mermaid export extension

---

## Integration with Documentation

These diagrams are referenced in:
- [README.md](README.md) - Main documentation
- [DESIGN.md](DESIGN.md) - Detailed design documentation

---

**Last Updated**: 2025-01-05  
**Version**: 1.0  
**Author**: Prihantoosa
