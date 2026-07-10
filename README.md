# Ayush Integration Lab

A technical blog built with [Jekyll](https://jekyllrb.com/) and hosted on **GitHub Pages**.

Topics: IBM API Connect, Kong Gateway, Middleware & Integration, POS Modernization, n8n Automation, and Tech Experiments.

## Live Site

Live site:

```
https://ayusshsharma.github.io/Integration-and-beyond/
```

Repository: [github.com/ayusshsharma/Integration-and-beyond](https://github.com/ayusshsharma/Integration-and-beyond)

## Quick Start — Publish to GitHub Pages

### Step 1: Push this project

```powershell
cd C:\Users\Admin\Projects\ayush-integration-lab
git remote add origin https://github.com/ayusshsharma/Integration-and-beyond.git
git push -u origin main
```

If the remote already exists, use:

```powershell
git remote set-url origin https://github.com/ayusshsharma/Integration-and-beyond.git
git push -u origin main
```

### Step 2: Enable GitHub Pages

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

Open [http://localhost:4000/Integration-and-beyond/](http://localhost:4000/Integration-and-beyond/)

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
