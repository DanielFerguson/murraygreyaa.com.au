# CSV Import, Lineage & Animal Assignment Design

## Overview

Import ~1,500 animal records from a CSV export into WordPress, expand the data model to accommodate new fields, link animals to their sire/dam via post relationships for family tree traversal, and enable admin bulk assignment of animals to users.

## Decisions

- **Owner mapping**: Import all animals unassigned; manual assignment via wp-admin later
- **Lineage storage**: ACF post object relationships (`sire_id`, `dam_id`) pointing to cattle_registration posts
- **Tree depth**: 3-generation pedigree chart on the detail page (animal → parents → grandparents)
- **Duplicates**: Skip rows where `registration_number` matches an existing post
- **Missing fields**: Leave blank (calving_ease, is_ai, is_et, is_twin, birth_weight)
- **Import method**: Single-page batch import in wp-admin (no AJAX chunking)
- **Assignment UI**: Bulk action on cattle admin list + author meta box on edit screen

## 1. Expanded ACF Fields

### New fields

| Field | Key | Type | Notes |
|---|---|---|---|
| Registration Number | `registration_number` | Text | Full reg like "RIB G70". Unique identifier. |
| Stud Name | `stud_name` | Text | Breeder/stud (e.g., "Arki", "Balmoral") |
| Herd Book | `herd_book` | Number | HB value from CSV |
| Brand Tattoo | `brand_tattoo` | Text | BTat prefix (e.g., "RIB", "WIN") |
| Sire (animal link) | `sire_id` | Post Object → cattle_registration | Links to sire's post |
| Dam (animal link) | `dam_id` | Post Object → cattle_registration | Links to dam's post |
| Sire Herd Book | `sire_herd_book` | Number | SHB from CSV |
| Sire Grade | `sire_grade` | Text | SGrade from CSV |
| Dam Herd Book | `dam_herd_book` | Number | DHB from CSV |
| Dam Grade | `dam_grade` | Text | DGrade from CSV |

### Kept fields (text fallbacks)

`sire_name`, `sire_tattoo`, `dam_name`, `dam_tattoo` remain for animals whose parents aren't in the database. Display logic: if `sire_id` is set, show the linked animal as a clickable link; otherwise fall back to text fields.

## 2. CSV Import Admin Page

### Location

New submenu under "Cattle" → "Import Animals". Requires `manage_options` capability.

### Import flow

1. Upload form accepts `.csv` files
2. Preview first 10 rows in a table
3. Click "Import" → two-pass batch process:
   - **Pass 1**: Create cattle_registration posts (publish status)
   - **Pass 2**: Resolve sire/dam lineage links by matching registration numbers
4. Redirect with results summary

### Column mapping

| CSV | ACF Key | Transform |
|---|---|---|
| Stud | `stud_name` | Direct |
| Name | `calf_name` + post title | Direct |
| AnmlSex | `sex` | 1→M, 2→F |
| AnmlDOB | `date_of_birth` | Parse DD-MMM-YY → Y-m-d |
| ColourLiteral | `colour` | Grey→G, Silver→S, Black→B, Dun→D |
| Regn | `registration_number` | Direct |
| HB | `herd_book` | Direct |
| AnmlGrade | `grade` | 1→PB, 2→A, 3→B, 4→C |
| BTat | `brand_tattoo` | Direct |
| Tat | `tattoo_number` | Direct |
| SHB | `sire_herd_book` | Direct |
| SRegn | `sire_tattoo` + lineage resolution → `sire_id` | Text + post lookup |
| SGrade | `sire_grade` | Direct |
| DHB | `dam_herd_book` | Direct |
| DRegn | `dam_tattoo` + lineage resolution → `dam_id` | Text + post lookup |
| DGrade | `dam_grade` | Direct |
| AnmlOwnerNo | Ignored | Manual assignment later |

### Duplicate detection

Query `registration_number` meta before inserting. Skip if exists.

## 3. Family Tree / Pedigree Display

### Layout

Horizontal 3-generation pedigree on `single-cattle_registration.php`:

```
                              ┌── Paternal Grand-Sire
              ┌── Sire ──────┤
              │               └── Paternal Grand-Dam
 Animal ──────┤
              │               ┌── Maternal Grand-Sire
              └── Dam ────────┤
                              └── Maternal Grand-Dam
```

### Implementation

- Query `sire_id`/`dam_id` for the animal, then for each parent (up to 7 animals)
- Each ancestor box: name, registration number, clickable link to their detail page
- Text fallback for unlinked parents (greyed-out box, no link)
- CSS flexbox layout with connecting lines
- Responsive: vertical stack with indentation on mobile

## 4. Admin Bulk Assignment

### Bulk action on cattle list

- "Change Owner" bulk action in the WordPress admin post list dropdown
- Searchable user dropdown filtered to approved members
- Sets `post_author` on selected cattle posts

### Dashboard integration

- Existing dashboard already queries by `post_author` — no changes needed
- Imported animals are `publish` status → green "Approved" badge automatically

### Search enhancements

- Add `registration_number` and `stud_name` to the animal search text query

## Files to modify

- `inc/cattle-registration.php` — New ACF fields, import page, bulk action
- `single-cattle_registration.php` — Pedigree chart display
- `page-animal-search.php` — Search by registration number and stud name
- `page-register-cattle.php` — Add new fields to the registration form
- `page-dashboard.php` — Minor updates if needed for new field display
