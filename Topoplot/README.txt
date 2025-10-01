# Complete Project Structure for PythonAnywhere

mysite/
├── flask_app.py                 # Main Flask application
├── requirements.txt             # Python dependencies
├── wsgi.py                     # WSGI configuration untuk PythonAnywhere
├── config.py                   # Configuration settings
├── static/
│   ├── css/
│   │   └── style.css           # CSS untuk frontend (optional)
│   ├── js/
│   │   └── main.js             # JavaScript untuk frontend (optional)
│   └── temp/                   # Temporary files storage
├── templates/
│   ├── index.html              # Main frontend page
│   ├── api_docs.html           # API documentation page
│   └── test.html               # Testing interface
├── utils/
│   ├── __init__.py
│   ├── data_processor.py       # Data processing utilities
│   └── plot_generator.py       # Plot generation utilities
└── tests/
    ├── __init__.py
    ├── test_api.py             # API tests
    └── sample_data.json        # Sample data untuk testing
```

## Core Files You Need

### 1. Main Application Files
- `flask_app.py` - Main Flask app with all API endpoints
- `requirements.txt` - All Python packages needed
- `wsgi.py` - WSGI configuration for PythonAnywhere

### 2. Optional Helper Files
- `config.py` - Settings and configuration
- `models/data_models.py` - Data validation
- `utils/data_processor.py` - Data processing logic
- `utils/plot_generator.py` - Plot generation logic

### 3. Frontend Demo (Optional)
- `templates/index.html` - Demo web interface
- `static/js/api-client.js` - JavaScript API client

### 4. Documentation
- `README.md` - Project setup and usage instructions
- `templates/api_docs.html` - API documentation page

## Minimum Required Files for Basic Setup
For minimal setup, you only need:
1. `flask_app.py`
2. `requirements.txt`
3. `wsgi.py`

## File Upload Order for PythonAnywhere
1. Create folder structure
2. Upload `requirements.txt` first
3. Install dependencies via console
4. Upload all Python files
5. Configure web app
6. Upload static/template files (if needed)