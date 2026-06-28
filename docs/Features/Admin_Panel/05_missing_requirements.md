# Admin Panel — Implementation Notes & Discovered Gaps

These were discovered while implementing the feature against the existing schema/design and were fixed as part of this implementation.

## 1. Missing `image` column on `sub_sub_categories`

The feature analysis (`01_feature_analysis.png`) and endpoints design (`02_endpoints_design.png`) both call for image upload on Sub-Sub Categories, but the `sub_sub_categories` table had no `image` column (only `sub_categories` and `menu_items` had one).

**Fix:** added migration `2026_06_28_162634_add_image_to_sub_sub_categories_table.php` and added `image` to `SubSubCategory::$fillable`.

## 2. Category deletion is already DB-restricted, but needed app-level guards for clean error responses

`menu_items.sub_sub_category_id` uses `restrictOnDelete()`, while `sub_categories.main_category_id` and `sub_sub_categories.sub_category_id` use `cascadeOnDelete()`. Deleting a Main/Sub category that transitively owns menu items would previously bubble up as a raw `QueryException` (500) once the cascade hit the lowest-level restrict.

**Fix:** `Admin\CategoryService` now checks for existing menu items in the category's lineage before deleting and returns a `422` with a clear message, for all three levels (Business Rule #2).

## 3. Staff/Delivery deletion guards

`deliveries.staff_user_id` already uses `restrictOnDelete()` (an employee with assigned deliveries can't be removed at the DB level), and `orders.customer_id` uses `restrictOnDelete()` too. `Admin\StaffService::deleteUser()` now checks both cases up front (plus "cannot delete self") to return friendly `422` responses instead of a raw SQL exception (Business Rules #3 and #7).

## 4. `StaffDetailFactory` was empty

`database/factories/StaffDetailFactory.php` had no `definition()`, which fails on `staff_details`' `NOT NULL` columns (`job_title`, `salary`, `hire_date`, `shift`, `department`) as soon as anything other than empty creation is attempted. This blocked writing feature tests for Staff Management.

**Fix:** filled in `definition()` with sensible fake values, matching the pattern of other factories in the project.
