# PDF Accessibility Compliance Plan

## Current Status (2026-03-17)

### Completed
| Feature | Status | Notes |
|---------|--------|-------|
| Document metadata (title, author, lang) | Done | `/Lang en-US`, DisplayDocTitle |
| MarkInfo / Marked flag | Done | |
| Structure tree (StructTreeRoot) | Done | Nested: Document → Sect → elements |
| Nested /Sect grouping | **Done** | Section-boxed blocks wrapped in /Sect |
| Reading order (MCR+OBJR interleaved) | **Done** | Per-block interleaving |
| Form field TU tooltips | Done | All widgets have accessible names |
| Required field flags (/Ff) | Done | Bit 0x2 set |
| Print flag (/F 4) on annotations | Done | All Widget + Link annotations |
| Marked content (BDC/EMC) | Done | tagContent() + tagArtifact() |
| Link tagging (/Link struct elements) | Done | OBJR + Alt text with URL |
| Tab order (/Tabs /S) | Done | Structure-based on all pages |
| ParentTree | Done | MCR arrays + OBJR individual refs |
| Graceful degradation | Done | Falls back to untagged if PDFOperator unavailable |

### Remaining Gaps (Minor)

#### 1. List Structure in Notice/Info-Paragraph Blocks
**Priority:** Low
**Effort:** ~30 lines
**Issue:** Bullet lists (`- item1\n- item2`) in notice and info-paragraph blocks are tagged as `/P` (paragraph). PDF/UA best practice is `/L` → `/LI` → `/LBody` (List → ListItem → ListBody).
**Fix:** In the PDF export cases for `notice` and `info-paragraph`, detect lines starting with `- ` and wrap them in `/L`/`/LI`/`/LBody` structure instead of `/P`. Requires creating nested structure elements during the MCR phase.
**Impact:** Screen readers would announce "list of N items" instead of reading each bullet as a separate paragraph.

#### 2. Heading Level Specificity
**Priority:** Very Low
**Effort:** ~5 lines
**Issue:** Section headers use `/H` (generic heading) instead of `/H1`. Grey bars use `/H2`. While `/H` is valid per PDF 1.7, PDF/UA validators prefer numbered headings for hierarchy.
**Fix:** Change `tagContent('H', ...)` to `tagContent('H1', ...)` in section header rendering (section, student-info, course-info, address, text-field showSection, dropdown showSection, office-use). Keep `/H2` for grey bar (section-title).
**Impact:** More explicit heading hierarchy for screen readers.

#### 3. Table Structure for Grid Layouts
**Priority:** Low
**Effort:** ~100 lines
**Issue:** Multi-column grid blocks (student-info, course-info, address, text-field grids) render as positioned text + form fields. They're visually tabular but not tagged as `/Table` → `/TR` → `/TD`.
**Fix:** For blocks that render 2+ columns of label+field pairs, wrap in `/Table`/`/TR`/`/TD` structure instead of flat `/P`+`/Form`.
**Impact:** Screen readers would navigate grid fields as table cells. However, since these are simple label-field pairs (not data tables), the current `/P`+`/Form` approach is arguably more semantically correct. **This may not be needed** — only implement if a PDF/UA validator specifically flags it.

#### 4. Artifact Tagging Completeness Audit
**Priority:** Very Low
**Effort:** Audit only
**Issue:** All decorative content should be tagged as Artifact. We tag backgrounds, borders, lines, and footer text. Need to verify no decorative element is accidentally tagged as real content.
**Fix:** Export a complex form and run through PAC (PDF Accessibility Checker) to identify any mis-tagged content.

#### 5. Color Contrast Verification
**Priority:** Low
**Effort:** Audit only
**Issue:** WCAG 2.1 AA requires 4.5:1 contrast ratio for normal text, 3:1 for large text. Our colors appear compliant (dark text on light backgrounds) but haven't been formally verified.
**Fix:** Run all color combinations through a contrast checker. Key pairs to verify:
- `#4a4a4a` (label text) on `#fafafa` (section bg) — likely ~8:1 ✓
- `#BA0C2F` (red headers) on `#fafafa` (section bg) — likely ~5:1 ✓
- White text on `#666` (grey bar) — likely ~5:1 ✓
- `rgb(0.4,0.4,0.4)` footer text on white — likely ~5:1 ✓

## Validation Approach

1. Export a form with all 16 block types
2. Run through **PAC 2024** (PDF Accessibility Checker) — free tool from PDF/UA Foundation
3. Run through **Adobe Acrobat Pro** accessibility checker
4. Test with **NVDA** or **JAWS** screen reader on the exported PDF
5. Address any failures from above tools

## Architecture Notes

The structure tree is now built in a single per-block pass rather than two separate passes (MCR then OBJR). This ensures:
- Reading order matches visual order (label → field, not all-labels → all-fields)
- Section grouping provides navigation landmarks for screen readers
- Block-level annotation tracking via pre/post annotation snapshots

Tree structure:
```
StructTreeRoot
  └── Document
        ├── Figure (logo)
        ├── H (form title)
        ├── P (university address)
        ├── Sect ← student-info block
        │    ├── H (section header)
        │    ├── P (label) → Form (field)  ← interleaved
        │    └── P (label) → Form (field)
        ├── P (notice text)  ← flat, no section box
        ├── Sect ← course-info block
        │    ├── H (section header)
        │    └── Form (fields...)
        ├── Form (checkbox)  ← flat
        ├── Sect ← office-use block
        │    └── ...
        └── (footer is Artifact, not in tree)
```
