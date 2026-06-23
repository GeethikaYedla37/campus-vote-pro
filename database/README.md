# Database Guide

Use `schema.sql` to create the complete MySQL database.

## Import

In phpMyAdmin:

1. Open `http://localhost:8080/phpmyadmin`
2. Click **Import**
3. Choose `database/schema.sql`
4. Click **Go**

It creates the database:

```sql
campus_vote_pro
```

## Relationship Summary

- One category has many candidates.
- One student can vote once per category.
- One vote belongs to one student, one candidate, and one category.
- Results are calculated using `COUNT(votes.id)`.
- Feedback messages are stored for admin review.

## Main Result Query

```sql
SELECT ec.name AS category,
       c.name AS candidate,
       COUNT(v.id) AS votes
FROM candidates c
JOIN election_categories ec ON ec.id = c.category_id
LEFT JOIN votes v ON v.candidate_id = c.id
GROUP BY c.id, ec.name, c.name
ORDER BY ec.name, votes DESC;
```

## Feedback Table

Messages submitted from the student dashboard are stored in:

```text
feedback_messages
```
