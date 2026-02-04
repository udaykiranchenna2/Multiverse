# MadeItEasyTools/Multiverse 🌌

**Run Python, Node.js, and other languages natively inside your Laravel application.**

This package bridges the gap between PHP's web dominance and Python's data supremacy. It allows you to run "Workers" written in other languages as if they were native Laravel classes.

## ✨ Features

- **🚀 Performance Optimized**: Sub-500ms execution times (via pre-installed environments).
- **🛡️ Secure**: Built-in Configurable Static Analysis to block dangerous commands (`rm -rf`, etc.).
- **🐍 Python Native**: Comes with first-class support for Python (v3.x).
- **📦 Shared Dependencies**: One `requirements.txt` to rule them all (saves disk space).
- **🛠️ Artisan Integration**: `make:worker`, `multiverse:install`, `multiverse:update`.

## 📦 Installation

```bash
composer require madeiteasytools/multiverse
```

### 1. Setup Python Environment

```bash
php artisan multiverse:install --lang=python
```

This creates a `multiverse/` directory, sets up a virtual environment, and **automatically updates your `.gitignore`** to exclude the venv and output directories.

### 2. Configure (Optional)

Publish the configuration to customize security rules or paths.

```bash
php artisan vendor:publish --tag=multiverse-config
```

## 🚀 Usage

### Create a Worker

```bash
php artisan make:worker image_processor --lang=python
```

### Write Your Python Logic

Edit `multiverse/python/image_processor/main.py`. The input and output are standard JSON.

```python
import sys
import json

def main():
    # 1. Read Input
    data = json.loads(sys.stdin.read())

    # 2. Do "Magic" (AI, Scraping, etc.)
    result = {"status": "success", "processed": data['image_url']}

    # 3. Print Output
    print(json.dumps(result))

if __name__ == "__main__":
    main()
```

### Run It (From Laravel)

```php
use MadeItEasyTools\Multiverse\Facades\MultiWorker;

$result = MultiWorker::run('image_processor', ['image_url' => 'https://example.com/image.jpg']);
```

### Add Dependencies

1. Add packages to `multiverse/python/requirements.txt` (e.g., `numpy`, `opencv-python-headless`).
2. Run update:
    ```bash
    php artisan multiverse:update --lang=python
    ```

## 🔒 Security

This package includes a **Static Code Analyzer** that blocks workers containing dangerous patterns.
Customize banned words in `config/multiverse.php`:

```php
'security' => [
    'scan_for_dangerous_code' => true,
    'dangerous_patterns' => [
        'rm -rf' => 'destructive deletion detected',
        'mkfs' => 'formatting command detected',
    ],
],
```

## 📄 License

MIT
