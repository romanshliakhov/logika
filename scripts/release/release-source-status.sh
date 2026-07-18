#!/usr/bin/env bash

set -euo pipefail

source_root="$(cd "${1:-$PWD}" && pwd -P)"
expected_branch="${RELEASE_SOURCE_BRANCH:-wordpress}"
acknowledgements="$source_root/scripts/release/release-source-acknowledgements"
actual_branch="$(git -C "$source_root" branch --show-current)"

if [[ -z "$actual_branch" && "${GITHUB_REF_NAME:-}" == "$expected_branch" ]]; then
  actual_branch="$GITHUB_REF_NAME"
fi

if [[ "$actual_branch" != "$expected_branch" ]]; then
  echo "Release source must be on branch $expected_branch: $source_root" >&2
  exit 1
fi

if [[ "${RELEASE_SOURCE_IGNORE_OUTSIDE_EDITS:-}" == "1" ]]; then
  exit 0
fi

dirty=0
while IFS= read -r worktree; do
  [[ -d "$worktree" ]] || continue
  [[ "$(cd "$worktree" && pwd -P)" == "$source_root" ]] && continue
  changes="$(git -C "$worktree" status --porcelain -- source wordpress/wp-content | sed '/\/\.DS_Store$/d')"
  [[ -z "$changes" ]] && continue
  worktree_branch="$(git -C "$worktree" branch --show-current)"
  fingerprint="$(printf '%s\n' "$changes" | sha256sum | awk '{print $1}')"
  if awk -v branch="$worktree_branch" -v fingerprint="$fingerprint" '$1 == branch && $2 == fingerprint { found = 1 } END { exit !found }' "$acknowledgements"; then
    printf 'Acknowledged outside edits in %s (%s)\n' "$worktree" "$worktree_branch" >&2
    continue
  fi
  printf 'Untransferred WordPress/static edits in %s:\n%s\n' "$worktree" "$changes" >&2
  dirty=1
done < <(git -C "$source_root" worktree list --porcelain | sed -n 's/^worktree //p')

if ((dirty)); then
  echo "Transfer or resolve these edits before building the canonical release." >&2
  exit 1
fi
