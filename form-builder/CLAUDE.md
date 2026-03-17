# Form Builder - CLAUDE.md

## Overview

Single-page WYSIWYG form builder (`index.html`) that generates fillable PDF forms for the UGA Office of the Registrar. Uses PDFLib for export and a PHP/RODS API backend for template storage. No framework — vanilla JS, Tailwind CSS (CDN), and inline `<style>` for the preview.

## Architecture

```
index.html       — ALL app code: HTML, CSS, JS (3000+ lines, single file)
api.php          — PHP backend for save/load/list form templates (RODS API)
templates/       — Server-side JSON template storage
```

### Three-Panel Layout
- **Left**: Block library (reusable + custom block buttons)
- **Center**: Live WYSIWYG preview (`.preview-page`, 8.5x11in)
- **Right**: Properties panel for selected block

### Data Flow
```
blocks[] array → renderBlock(b) → HTML preview
blocks[] array → exportPDF()    → PDFLib fillable PDF
```

### Key State Variables
```js
let blocks = [];            // Array of block config objects
let selectedId = null;      // Currently selected block ID
let letterheadTitle = '';    // Editable letterhead title
let idCounter = 0;          // Auto-increment for block IDs
```

### Key Functions
| Function | Purpose |
|----------|---------|
| `addBlock(type)` | Create block from template, push to blocks[] |
| `render()` | Calls renderPreview() + renderProps() |
| `renderPreview()` | Rebuild entire preview DOM from blocks[] |
| `renderBlock(b)` | Switch on b.type → return HTML string |
| `renderProps()` | Show properties panel for selectedId |
| `exportPDF()` | Generate fillable PDF via PDFLib |
| `saveToServer()` | POST to RODS API |
| `updateProp(prop, val)` | Update selected block property, re-render |

## Block Types

| Type | CSS Wrapper | Has Section Box | Notes |
|------|-------------|-----------------|-------|
| `section` | `.preview-section` | Yes (always) | Empty section header only |
| `section-title` | inline style | No | Grey bar, white uppercase text |
| `notice` | `.preview-notice` | No | Red left border, bold title, uses `markdownToHtml()` |
| `text-field` | `.preview-section` or `<div>` | Optional (`showSection`) | Multi-row, multi-column grid |
| `textarea` | `<div>` | No | Variable height via `b.rows` |
| `checkbox` | inline border box | No | Floating label above border |
| `dropdown` | `.preview-section` or `<div>` | Optional (`showSection`) | Multi-dropdown grid |
| `student-info` | `.preview-section` | Yes (always) | Fixed: name row + ID/email row |
| `course-info` | `.preview-section` | Yes (always) | Fixed: 4-column CRN/prefix/number/hours |
| `address` | `.preview-section` | Yes (always) | Street/city/state/country/zip + options |
| `info-paragraph` | inline style | No | Tan background, uses `markdownToHtml()` |
| `signature` | `<div>` | No | Label + line + optional photo ID |
| `office-use` | `.preview-office-use` | Yes (always) | Black border, approval checkboxes |
| `footer` | inline style | No | Centered revision date |

## Styling / Design System

### Brand Colors
```
UGA Red:        #BA0C2F  (section headers, required *, selection outline)
Label text:     #4a4a4a
Field border:   #cfcfcf
Section bg:     #fafafa
Section border: #e2e2e2
Header line:    #e0e0e0
Info paragraph: #f5f0eb (tan)
Notice bg:      #fff8f5
Grey bar:       #666
```

### Typography
```
Body font:      Inter (sans-serif)
Letterhead:     Georgia (serif)
Section header: 14px, 700, uppercase, letter-spacing 0.08em, color #BA0C2F
Field label:    13px, 600, color #4a4a4a
Field text:     11px
Notice text:    12px
Checkbox text:  11px
Info paragraph: 11px body, 10px title
Footer:         10px italic
```

---

## CRITICAL: Spacing & Layout Standards

**All block types follow these standardized spacing rules in BOTH the HTML preview AND the PDF export.**

### How Spacing Works

**Preview:** Every block is wrapped in a `.block-item` div by `renderPreview()`. The `.block-item` class provides `margin-bottom: 10px` — this is the SINGLE source of truth for inter-block spacing. Individual blocks must NOT add their own outer margins.

**PDF:** The `exportPDF()` function uses named constants for all spacing. After each block's case, `y` is decremented by the block height plus `BLOCK_GAP` (10pt).

### Preview (HTML) Spacing Tokens

| Token | Value | Where Applied |
|-------|-------|---------------|
| **Block gap** | `margin-bottom: 10px` | `.block-item` (wrapper around every block) |
| **Section inner padding** | `padding: 8px 12px` | `.preview-section` |
| **Section header gap** | `margin-bottom: 8px` | `.preview-section-header` |
| **Label-to-field gap** | `margin-bottom: 3px` | `.preview-field-label` |
| **Field bottom margin** | `margin-bottom: 6px` | `.preview-field-line` / `.preview-field-multiline` |
| **Grid column gap** | `gap: 10px` | All multi-column grids |
| **Row bottom margin** | `margin-bottom: 8px` | Between multi-field rows in text-field |

### PDF Export Spacing Constants

These are defined at the top of `exportPDF()` and used by ALL block types:

```js
const BLOCK_GAP = 10;       // Space between blocks (matches .block-item margin-bottom)
const LABEL_GAP = 4;        // Space between label text and field below
const FIELD_HEIGHT = 18;    // Standard fillable field height
const SECTION_PAD = 10;     // Inner padding from section box edges
const GRID_GAP = 10;        // Gap between grid columns
const ROW_GAP = 6;          // Vertical gap between field rows
```

### Page Dimensions (PDF)
```js
const pageWidth = 612;      // 8.5in at 72dpi
const pageHeight = 792;     // 11in at 72dpi
const mTop = 36;            // 0.5in
const mSide = 43;           // 0.6in
const mBottom = 29;         // 0.4in
const contentWidth = 526;   // pageWidth - (mSide * 2)
```

### Spacing Rules

1. **Block outer spacing is handled by the wrapper, not the block.** In preview, `.block-item { margin-bottom: 10px }` handles all inter-block gaps. In PDF, each case ends with `y -= blockHeight + BLOCK_GAP`. Individual blocks must NOT add their own outer margin/margin-bottom.

2. **Labels sit directly above their inputs.** Preview: `margin-bottom: 3px` on `.preview-field-label`. PDF: `y -= LABEL_GAP` (4pt) after drawing label text. Do NOT add `margin-top` on field lines.

3. **Section boxes contain all children.** `.preview-section` uses `padding: 8px 12px` internally. In PDF, inner content starts at `mSide + SECTION_PAD` and the section rect is pre-calculated to fit all content.

4. **Grid gaps are always 10px/10pt.** Both preview (`gap: 10px`) and PDF (`GRID_GAP = 10`) use the same value.

5. **Required asterisks are always red.** Preview: `<span style="color:#BA0C2F;">*</span>`. PDF: `drawText(' *', { color: red, font: fontBold })`. Never use plain text ` *`.

6. **Checkbox floating labels need margin-top: 8px** on the border box div to make room for the absolutely-positioned label. Do not remove this.

---

## Bold / Rich Text Rendering

### Shared Function: `markdownToHtml(text)`
Converts `**bold**` to `<strong>`, `- items` to `<ul><li>`, and `\n` to `<br>`. Used by:
- `notice` block (preview)
- `info-paragraph` block (preview)

### Reverse: `htmlToMarkdown(html)`
Converts `<strong>` back to `**bold**` for storage. Used by contenteditable editors.

### PDF Bold Rendering Pattern
`info-paragraph` uses bold-aware wrapping via `wrapInfoBoldLine()`: bold markers are parsed
**before** word wrapping, producing `{text, bold}` tokens per word. This ensures bold spans
that cross word-wrap boundaries render correctly. Other blocks (checkbox, notice) still use
the simpler inline regex split:
```js
const parts = line.split(/(\*\*[^*]+\*\*)/g);
parts.forEach(part => {
    if (part.startsWith('**') && part.endsWith('**')) {
        const boldText = part.slice(2, -2);
        currentPage.drawText(boldText, { x, y, size, font: fontBold, color });
        x += fontBold.widthOfTextAtSize(boldText, size);
    } else if (part) {
        currentPage.drawText(part, { x, y, size, font: font, color });
        x += font.widthOfTextAtSize(part, size);
    }
});
```

### Checkbox Bold
Checkbox options support TWO bold modes:
- `opt.bold: true` — entire option text rendered in bold
- Inline `**bold**` markdown — parsed per-word like notice/info-paragraph

Both modes work in preview AND PDF export.

---

## Text Wrapping in PDF Export

### Shared Helper: `wrapText(text, maxWidth, useFont, fontSize)`
Word-wraps text into an array of line strings that fit within `maxWidth`. Defined at the top of `exportPDF()` and used by all block types with titles/headers.

### Section Header Pattern (with wrapping)
Blocks with section boxes (`section`, `student-info`, `course-info`, `address`, `text-field` showSection, `dropdown` showSection) use the same wrapped header pattern:
```js
const titleLines = wrapText(b.title.toUpperCase(), contentWidth - SECTION_PAD * 2, fontBold, 11);
const headerExtra = (titleLines.length - 1) * 14;
const boxH = baseHeight + headerExtra;  // Box grows with extra title lines
// Draw each title line
titleLines.forEach((line, i) => {
    drawText(line, { y: y - 14 - i * 14 });
});
// Underline shifts down
const underlineY = y - 20 - headerExtra;
// Content start shifts down
y -= 30 + headerExtra;
```

### Other Wrapped Titles
- **`section-title`** (grey bar): Line height 13px, bar grows with extra lines
- **`office-use`**: Line height 12px at size 9, box grows with extra lines
- **`textarea`**: Label wraps inline (implemented separately)
- **`info-paragraph`**: Uses `wrapInfoTitle()` (implemented separately)
- **`text-field` field labels**: Uses `wrapLabel()` (implemented separately)

---

## Dual Rendering Pipeline

Every visual change must be made in TWO places:

1. **`renderBlock(b)`** — HTML string for WYSIWYG preview
2. **`exportPDF()`** — PDFLib coordinate-based drawing for PDF

These are completely independent code paths. A fix in one does NOT affect the other. **Always update both.**

### PDF Coordinate System
- Origin is **bottom-left** (0,0)
- `y` starts at `pageHeight - mTop` and decrements downward
- `ensureSpace(h)` checks if `y - h < mBottom`, creates new page if needed
- All positions are in **points** (1 inch = 72 points)

## Common Patterns

### Adding a New Block Type
1. Add template in `templates` object (~line 539)
2. Add `case` in `renderBlock()` for HTML preview
3. Add `case` in `renderProps()` for properties panel
4. Add `case` in `exportPDF()` for PDF rendering
   - Wrap headings in `tagContent('H', title, () => { ... })`
   - Wrap field labels in `tagContent('P', label, () => { ... })`
   - Wrap decorative elements (backgrounds, borders, lines) in `tagArtifact(() => { ... })`
5. Add button in left sidebar HTML

### Section Box Pattern (Preview)
```html
<div class="preview-section">
    <div class="preview-section-header">TITLE</div>
    <!-- fields here -->
</div>
```
No outer margin needed — `.block-item` handles it.

### Section Box Pattern (PDF)
```js
ensureSpace(totalH + BLOCK_GAP);
tagArtifact(() => {
    currentPage.drawRectangle({ x: mSide, y: y - totalH, width: contentWidth, height: totalH,
        color: rgb(0.98,0.98,0.98), borderColor: rgb(0.89,0.89,0.89), borderWidth: 0.5 });
});
tagContent('H', title, () => {
    currentPage.drawText(title.toUpperCase(), { x: mSide + SECTION_PAD, y: y - 14, size: 11, font: fontBold, color: red });
});
tagArtifact(() => {
    currentPage.drawLine({ start: {x: mSide + SECTION_PAD, y: y-20}, end: {x: pageWidth - mSide - SECTION_PAD, y: y-20},
        thickness: 0.5, color: rgb(0.88,0.88,0.88) });
});
y -= 30; // past header
// ... render fields, wrapping labels in tagContent('P', ...) ...
y -= totalH + BLOCK_GAP; // advance past block
```

### Required Asterisk
- Preview: `<span style="color:#BA0C2F;"> *</span>`
- PDF: `currentPage.drawText(' *', { ..., font: fontBold, color: red });`

## PDF Accessibility (Tagged PDF)

The exported PDFs include a full tag structure for screen reader navigation of ALL content — both interactive form fields and static visual content (headings, labels, paragraphs, images).

### What's Implemented
- **Document metadata**: title, author, subject, language (`/Lang en-US`), producer, creator
- **`/ViewerPreferences << /DisplayDocTitle true >>`**: Shows document title in viewer title bar
- **`/MarkInfo << /Marked true >>`**: Declares the PDF as tagged
- **`/StructTreeRoot`**: Nested structure tree: `/Document` → `/Sect` (for section-boxed blocks) → `/H`, `/H2`, `/P`, `/Figure`, `/Form`, `/Link` elements
- **Marked content (BDC/EMC)**: All static text and images wrapped in marked content operators via `tagContent()` / `tagArtifact()`
- **Form field `/TU` tooltips**: Every widget annotation has an accessible name via `setFieldAccessibility()`
- **Required `/Ff` flags**: Bit 0x2 set on required fields
- **Print flag `/F 4`**: Set on all widget and link annotations (PDF/UA requirement)
- **`/StructParent` + `/StructParents` + `/ParentTree`**: Widgets/links use `/StructParent` (singular), pages use `/StructParents` (plural) for MCR arrays
- **`/Tabs /S`**: Structure-based tab order on every page
- **Link tagging**: URL/email link annotations get `/Link` structure elements with alt text

### How It Works — Two-Phase Tagging

**Phase 1: During block rendering (inline)**
1. `setFieldAccessibility(field, label, required)` sets `/TU` and `/Ff` on each widget
2. Radio button options get per-widget TU after `addOptionToPage()`
3. `tagContent(structType, altText, drawFn)` wraps static content draws:
   - Creates MCID properties dict, stores in page `/Resources/Properties`
   - Pushes `BDC` operator (via `PDFLib.PDFOperator.of('BDC', ...)`) before draw calls
   - Pushes `EMC` operator after draw calls
   - Records `{mcid, structType, altText}` in `pageTagEntries` Map for later
4. `tagArtifact(drawFn)` wraps decorative elements in `BMC Artifact` / `EMC`

**Phase 2: After block loop (retroactive, per-block grouping)**
1. Iterates blocks in order (letterhead first as blockIdx -1, then each block 0..N)
2. For section-boxed blocks (`section`, `student-info`, `course-info`, `address`, `office-use`, `text-field`+showSection, `dropdown`+showSection): creates `/Sect` wrapper element under `/Document`
3. For each block: adds MCR elements (tagged static content) then OBJR elements (annotations) — **interleaved per block** for correct reading order
4. Annotation tracking: `snapshotAnnotsBefore()`/`findNewAnnots()` captures which annotations each block created
5. Safety net: any untracked annotations get tagged flat under `/Document`
6. Builds page `StructParents` arrays, unified ParentTree, and `StructTreeRoot`
7. Wrapped in try/catch — structure tree errors don't break PDF export

### Tagged Content Types

| Content | Struct Type | Tagged By |
|---------|------------|-----------|
| Section headers | `/H` | `tagContent('H', ...)` |
| Grey bar titles | `/H2` | `tagContent('H2', ...)` |
| Field labels, body text | `/P` | `tagContent('P', ...)` |
| Logo image | `/Figure` | `tagContent('Figure', ...)` with alt text |
| Form fields | `/Form` | OBJR (retroactive pass) |
| URL/email links | `/Link` | OBJR (retroactive pass) |
| Backgrounds, borders, lines, footer | `Artifact` | `tagArtifact(...)` |

### Graceful Degradation
If `PDFLib.PDFOperator` is not available (checked at export start), `tagContent` and `tagArtifact` call `drawFn()` directly without BDC/EMC. The PDF generates identically to pre-tagging behavior. If any individual `tagContent` call fails, tagging is disabled for the remainder and content is still drawn.

### Key Low-Level APIs Used
```js
pdf.context.obj({...})      // Create direct PDF objects (dict, array, etc.)
pdf.context.register(obj)   // Register as indirect object, returns PDFRef
pdf.context.lookup(ref)     // Dereference PDFRef to underlying object
pdf.catalog.set(name, val)  // Set entries on the PDF catalog dictionary
page.node.set(name, val)    // Set entries on page dictionary
page.node.lookup(name)      // Get + dereference from page dictionary
PDFHexString.fromText(str)  // Unicode-safe string encoding
PDFLib.PDFOperator.of(name, args)  // Create content stream operator (BDC, BMC, EMC)
currentPage.pushOperators(op)      // Push operator into page content stream
```

## Dependencies

- **Tailwind CSS** (CDN) — Utility classes for layout panels
- **PDFLib** (CDN, v1.17.1) — PDF generation with fillable form fields
- **Lucide Icons** (CDN) — UI icons
- **Inter font** (Google Fonts) — Body text
- **api.php** — RODS REST API proxy for template persistence
