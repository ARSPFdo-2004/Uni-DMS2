#!/usr/bin/env bash
# reorganize_images.sh

cd images || exit 1

mkdir -p gallery universities hero icons

# Hero
mv -f Graduation_hats.jpg Pattern.jpg hero/ 2>/dev/null || true
for f in hero/*; do
  if [ -f "$f" ]; then
    lower=$(echo "$f" | tr '[:upper:]' '[:lower:]')
    [ "$f" != "$lower" ] && mv "$f" "$lower"
  fi
done

# Icons
for ext in png jpg jpeg; do
  mv -f *icon*.$ext icons/ 2>/dev/null || true
  for f in icons/*.$ext; do
     [ -f "$f" ] && mv "$f" "$(echo "$f" | tr '[:upper:]' '[:lower:]')" 2>/dev/null || true
  done
done

# Universities
for keyword in university colombo sabaragamuwa seusl wellassa wayamba; do
  for f in *"$keyword"*.*; do
    if [ -f "$f" ]; then
      new_name=$(echo "$f" | tr '[:upper:]' '[:lower:]' | tr -d ' ')
      mv "$f" "universities/$new_name"
    fi
  done
done
for f in *University*.*; do
  if [ -f "$f" ]; then
    new_name=$(echo "$f" | tr '[:upper:]' '[:lower:]' | tr -d ' ')
    mv "$f" "universities/$new_name"
  fi
done

# Gallery - sort by size descending and rename
# Get files matching gallery* or graduation*
files_to_sort=""
for f in gallery*.* Graduation*.*; do
    if [ -f "$f" ]; then
        size=$(wc -c < "$f" | tr -d ' ')
        files_to_sort="$files_to_sort$size $f\n"
    fi
done

if [ -n "$files_to_sort" ]; then
    counter=1
    echo -e "$files_to_sort" | sort -nr | while read -r size filename; do
        if [ -n "$filename" ]; then
            ext="${filename##*.}"
            ext_lower=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
            mv "$filename" "gallery/gallery${counter}.${ext_lower}"
            counter=$((counter + 1))
        fi
    done
fi

echo "Images reorganized successfully!"
