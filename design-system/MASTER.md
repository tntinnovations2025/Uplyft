# UPLYFT Design System Master Specification
**Style:** Bright Liquid Glass / Radiant Glassmorphism  
**Theme:** Crisp Luminous Daylight Canvas with Radiant Instagram Spectrum Glows  
**Tech Stack:** Laravel 11/12 (Blade Components + Alpine.js) + Tailwind CSS  
**Version:** 2.0.0 • Bright Mode Production Standard  

---

## 1. Visual Identity & Design Principles

### 1.1 The "Bright Liquid Glass" Paradigm
Bright Liquid Glass combines the airy, clean aesthetics of modern daylight interfaces with the depth and chromatic vibrancy of Instagram's radiant energy:

1. **Multi-Layered Spatial Hierarchy:**
   - **Canvas Layer (Z: 0):** Pure crisp off-white canvas (`#f8fafc` / `#f1f5f9`) with ambient floating Instagram-tinted gradient blobs (Electric Crimson `#fd1d1d`, Vibrant Pink `#e1306c`, Radiant Purple `#833ab4`, Warm Gold `#fcb045`, and Cyan `#06b6d4`).
   - **Base Glass Surface (Z: 10):** Frosted translucent white (`rgba(255, 255, 255, 0.78)`), backdrop blur (`20px`), subtle 1px border (`rgba(226, 232, 240, 0.8)` / `border-slate-200/80`), top inner rim highlight (`inset 0 1px 0 rgba(255, 255, 255, 1)`).
   - **Interactive / Elevated Cards (Z: 20):** Elevated frosted white (`rgba(255, 255, 255, 0.90)`), soft ambient drop shadow (`0 10px 30px -5px rgba(0, 0, 0, 0.06), 0 4px 12px -2px rgba(0, 0, 0, 0.03)`).
   - **Floating Modals & Dropdowns (Z: 50+):** High blur (`32px`), pure white surface (`rgba(255, 255, 255, 0.95)`), crisp rim lighting.

2. **Specular Light Reflection:**
   - Every card incorporates an inner top rim highlight (`rgba(255, 255, 255, 0.90)` to `1.0`) ensuring crisp physical separation from the ambient background.

3. **High-Contrast Daylight Readability:**
   - Text color standards: Primary text `#0f172a` (Slate 900), Subheadings `#1e293b` (Slate 800), Muted secondary copy `#64748b` (Slate 500). All text contrast ratios $\ge 4.5:1$ (WCAG AA/AAA compliant).

---

## 2. Color Palette & Semantic Tokens

### 2.1 Bright Canvas & Surface Palette
```css
--canvas-bg:         #f8fafc; /* Slate 50 daylight base */
--canvas-card:       rgba(255, 255, 255, 0.85); /* Frosted glass cards */
--canvas-elevated:   rgba(255, 255, 255, 0.95); /* Elevated surfaces */
--glass-border:      rgba(226, 232, 240, 0.85); /* Border stroke */
--glass-rim:         rgba(255, 255, 255, 1.0);  /* Top inner highlight */
```

### 2.2 Radiant Instagram Accent Spectrum
| Role | Name | Hex | Usage |
|------|------|-----|-------|
| **Crimson / Red** | Electric Crimson | `#fd1d1d` | Primary gradient start, urgent actions |
| **Pink** | Vibrant Pink | `#e1306c` | Primary gradient core, active rings, glows |
| **Purple** | Radiant Purple | `#833ab4` | Primary gradient finish, AI badges |
| **Gold / Orange** | Radiant Gold | `#fcb045` | Secondary highlights, warnings, stars |
| **Cyan / Sky** | Electric Cyan | `#06b6d4` | Live stats, indicators, secondary links |
| **Emerald** | Vivid Emerald | `#10b981` | Success states, revenue values, online pills |

---

## 3. Typography & Spatial System

### 3.1 Font Families
- **Primary Body:** `'Plus Jakarta Sans', system-ui, -apple-system, sans-serif`
- **Headings & Metrics:** `'Outfit', 'Plus Jakarta Sans', sans-serif`
- **Monospace:** `'JetBrains Mono', monospace`

---

## 4. Blade Component Guidelines (Bright Mode)

### 4.1 `<x-glass-card>`
- Frosted translucent white background (`bg-white/80 backdrop-blur-xl border border-slate-200/80`).
- Top inner rim highlight (`shadow-[inset_0_1px_0_rgba(255,255,255,1)]`).
- Soft elevated shadow (`shadow-sm hover:shadow-md`).

### 4.2 `<x-glass-button>`
- Vibrant Instagram gradient fill (`bg-gradient-to-r from-[#fd1d1d] via-[#e1306c] to-[#833ab4]`).
- Crisp white bold text with radiant pink drop shadow (`shadow-lg shadow-pink-500/25`).

### 4.3 `<x-glass-input>`
- Translucent white background (`bg-white/90 border-slate-200`).
- Slate 900 high contrast input text with radiant focus glow ring (`focus:border-pink-500 focus:ring-pink-500/20`).
