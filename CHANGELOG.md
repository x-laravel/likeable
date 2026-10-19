# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-19

### Added

- `Contracts\CanLike` and `Concerns\Liker` for the model that likes, `Contracts\HasLikes` and
  `Concerns\Likeable` for the models that can be liked; one model can use both.
- Polymorphic likers, so any model can like; a liker likes a model once.
- `like()`, `unlike()` and `hasLiked()`; liking a model again returns the existing like.
- `likes()`, `likers()` with a configurable default liker model, `givenLikes()` and `likedItems()`
  for listing and counting.
- `withLikedBy()` scope and `isLikedBy()` to show the liked state of a list in one query.
- Cleanup of likes when their model or liker is deleted, and pruning of left-behind likes through
  `model:prune`, scheduled by the package with a configurable frequency and chunk size.
- `Events\Liked` and `Events\Unliked`, dispatched after commit.
- Configurable connection, table and model.

[1.0.0]: https://github.com/x-laravel/likeable/releases/tag/v1.0.0
