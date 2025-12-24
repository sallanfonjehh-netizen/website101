# Translation System User Guide

## How the Translation System Works

The local translation system has been implemented with the following features:

### ✅ What's Currently Translated

1. **Navigation Links** (All Pages)
   - Home
   - Services
   - About
   - Contact

2. **Common Buttons** (Automatically detected)
   - "GET STARTED" → Translates to selected language
   - "LEARN MORE" → Translates to selected language
   - "SUBMIT REQUEST" → Translates to selected language
   - "CHECK STATUS" → Translates to selected language

### 🔄 How to Use

1. **Select a Language**
   - Click the language selector button in the top-right navigation
   - Choose from: English, Spanish, German, French, Italian, or Portuguese
   - The button text will change to show the selected language

2. **What You'll See**
   - Navigation menu items will translate
   - Common buttons will translate
   - The language preference is saved automatically

### 📝 Current Limitations

The translation system currently focuses on:
- Navigation elements
- Common UI buttons
- Elements with `data-translate` attributes

**Full page content translation requires adding `data-translate` attributes to HTML elements.**

For example:
```html
<!-- This will translate -->
<h2 data-translate="services.title">Our Services</h2>

<!-- This won't translate automatically -->
<h2>Our Services</h2>
```

### 🔧 To Enable Full Page Translation

To translate more content on the page:

1. **Add data-translate attributes** to HTML elements:
```html
<h1 data-translate="hero.title">PRIVATE HACKERS</h1>
<p data-translate="hero.description">Expert ethical hackers...</p>
<button data-translate="hero.getStarted">GET STARTED</button>
```

2. **The translation keys** are defined in `/translations/*.json` files

3. **Format**: Use dot notation like `"hero.title"`, `"services.phoneAccess.title"`

### 📂 Translation Files

Located in `/translations/`:
- `en.json` - English
- `es.json` - Spanish (Español)
- `de.json` - German (Deutsch)
- `fr.json` - French (Français)
- `it.json` - Italian (Italiano)
- `pt.json` - Portuguese (Português)

Each file contains translations for:
- Navigation
- Hero section
- Services
- Features
- Contact forms
- Footer
- Ticket tracker

### 🐛 Troubleshooting

**Problem**: "I click the language button but the page doesn't translate"

**Solution**: 
1. Check browser console for errors (F12 → Console)
2. Verify translation files are accessible: `/translations/es.json`
3. Ensure elements have `data-translate` attributes
4. The navigation and buttons should translate automatically

**Problem**: "Only the navigation translates"

**Explanation**: This is expected behavior. Only elements with `data-translate` attributes and common buttons are auto-translated. To translate more content, add `data-translate` attributes to HTML elements.

### ✨ Benefits of Current System

- ⚡ **Fast**: No external API calls
- 🔒 **Private**: No data sent to Google
- 📴 **Offline**: Works without internet
- 💾 **Persistent**: Language choice is saved
- 🎨 **Customizable**: Easy to edit translation files

### 🚀 Next Steps for Full Translation

To add full page translation:

1. Open HTML files (index.html, services.html, etc.)
2. Add `data-translate` attributes to headings, paragraphs, and buttons
3. Use the translation keys from the JSON files
4. The system will automatically apply translations

Example:
```html
<!-- Before -->
<h2>Our Services</h2>
<p>Professional cybersecurity services</p>

<!-- After -->
<h2 data-translate="services.title">Our Services</h2>
<p data-translate="services.subtitle">Professional cybersecurity services</p>
```

The translation system is working correctly - it just needs `data-translate` attributes added to more HTML elements for comprehensive page translation.
