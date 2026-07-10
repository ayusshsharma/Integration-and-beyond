# Ayush Integration Lab

A technical blog built with [Jekyll](https://jekyllrb.com/) and hosted on **GitHub Pages**.

Topics: IBM API Connect, Kong Gateway, Middleware & Integration, POS Modernization, n8n Automation, and Tech Experiments.

## Live Site

After setup, your site will be at:

```
https://YOUR_GITHUB_USERNAME.github.io/ayush-integration-lab/
```

Or, if you use a `YOUR_GITHUB_USERNAME.github.io` repo:

```
https://YOUR_GITHUB_USERNAME.github.io/
```

## Quick Start — Publish to GitHub Pages

### Step 1: Update your GitHub username

Edit `_config.yml` and replace `YOUR_GITHUB_USERNAME` in three places:

```yaml
url: "https://YOUR_GITHUB_USERNAME.github.io"
github_username: YOUR_GITHUB_USERNAME
```

Also update `linkedin_username` and `email`.

**Important:** If your repo is named `YOUR_GITHUB_USERNAME.github.io`, set `baseurl: ""`.  
If your repo is named `ayush-integration-lab`, keep `baseurl: "/ayush-integration-lab"`.

### Step 2: Create the GitHub repository

1. Go to [github.com/new](https://github.com/new)
2. Repository name: `ayush-integration-lab` (or `YOUR_GITHUB_USERNAME.github.io` for a user site)
3. Set to **Public**
4. Do **not** initialize with README (we already have one)
5. Click **Create repository**

### Step 3: Push this project

```powershell
cd C:\Users\Admin\Projects\ayush-integration-lab
git init
git add .
git commit -m "Initial commit: Ayush Integration Lab blog"
git branch -M main
git remote add origin https://github.com/YOUR_GITHUB_USERNAME/ayush-integration-lab.git
git push -u origin main
```

### Step 4: Enable GitHub Pages

1. Open your repo on GitHub → **Settings** → **Pages**
2. Under **Build and deployment**:
   - Source: **Deploy from a branch**
   - Branch: **main** / **/ (root)**
3. Click **Save**
4. Wait 1–3 minutes — your site will be live at the URL shown

## Local Preview

Requires Ruby. On Windows, install [Ruby+Devkit](https://rubyinstaller.org/):

```powershell
gem install bundler
bundle install
bundle exec jekyll serve
```

Open [http://localhost:4000/ayush-integration-lab/](http://localhost:4000/ayush-integration-lab/) (adjust if `baseurl` is empty).

## Adding New Posts

Create a file in `_posts/` named `YYYY-MM-DD-my-post-title.md`:

```markdown
---
layout: post
title: "My New Post Title"
date: 2026-07-10 12:00:00 +0400
categories: [Kong Gateway]
tags: [kong, api]
---

Your content here. Use fenced code blocks:

​```yaml
key: value
​```
```

Commit and push — GitHub Pages rebuilds automatically.

## Customizing Colors & Typography

Edit CSS variables at the top of `assets/css/main.css`:

```css
:root {
  --color-bg: #F7F7F7;
  --color-header-footer: #1F1F1F;
  --color-accent: #0066CC;
  --color-text: #222222;
  --font-sans: "Inter", ...;
  --font-mono: "JetBrains Mono", ...;
}
```

## Project Structure

```
├── _config.yml          Site settings
├── _layouts/            Page templates
├── _includes/           Header, footer, sidebar
├── _posts/              Blog articles (Markdown)
├── category/            Category archive pages
├── assets/css/main.css  All styles
├── assets/js/main.js    Menu toggle, TOC, code labels
├── index.html           Homepage with hero
└── about.md             About page
```

## WordPress Theme (Legacy)

The original WordPress theme files are preserved in `wordpress-theme/` if you ever want to use them on a PHP host instead.

## License

GPL-2.0 (same as the original WordPress theme)
