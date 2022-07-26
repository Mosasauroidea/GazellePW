# Frontend Development Guide

## CSS Naming

> Follows [SUIT CSS naming conventions](https://github.com/suitcss/suit/blob/master/doc/naming-conventions.md)

### Component Naming

```jsx
// syntax: [<namespace>-]<ComponentName>[.is-stateOfComponent][-descendentName][--modifierName]

<button class="Button Button--modifier is-active ButtonSimple Box Box--modifier">
  <span class="Button-icon"></span>
  <span>
    <span>
      <span class="Button-deepVeryLongDescentName"></span>
    </span>
  </span>
</button>

// Compose multiple components: class="Button ButtonSimple Box"
```

### Variable Naming

```css
/* Syntax: --ComponentName[-descendant|--modifier][-onState]-(cssProperty|variableName) */

--Button-color
--Button-backgroundColor
--Button-onHover-color
--Button-deepVeryLongDescentName-color
--Button-deepVeryLongDescentName-onHover-color
```

## Javascript

### Don't not access DOM via class

Instead use `id` or `js attribute` to access the DOM. So that refactor the UI won't break the Javascript features.

```jsx
<div class="ButtonAdd" id="button-add" js="ButtonAdd">
document.querySelector('.ButtonAdd') // NO
document.querySelector('#button-add') // YES
document.querySelectorAll('js[ButtonAdd]') // YES
```

## Icon

- Must be in svg format.
- Must have `<svg class="icon" width=".." height=".." viewBox="..">`
- Custom Color: `<path fill="currentColor">`

### Create icon

```jsx
// src/icons/flag/hello.svg

<svg class="icon" width="24" height="24" viewBox="0 0 24 24">
  <path stroke="currentColor"></path>
</svg>
```

### Use icon

```php
// hello.php

<?= icon('flag/hello') ?>
```
