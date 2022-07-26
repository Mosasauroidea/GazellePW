# 前端开发指南

## CSS 命名

> 查看 [SUIT CSS 命名规则](https://github.com/suitcss/suit/blob/master/doc/naming-conventions.md)

### 组件命名

```jsx
// 语法: [<namespace>-]<ComponentName>[.is-stateOfComponent][-descendentName][--modifierName]

<button class="Button Button--modifier is-active ButtonSimple Box Box--modifier">
  <span class="Button-icon"></span>
  <span>
    <span>
      <span class="Button-deepVeryLongDescentName"></span>
    </span>
  </span>
</button>

// 使用多个组件: class="Button ButtonSimple Box"
```

### 变量命名

```css
/* 语法: --ComponentName[-descendant|--modifier][-onState]-(cssProperty|variableName) */

--Button-color
--Button-backgroundColor
--Button-onHover-color
--Button-deepVeryLongDescentName-color
--Button-deepVeryLongDescentName-onHover-color
```

## Javascript

### 不能使用 CSS Class 来访问 DOM

使用 id 或者 js 属性来访问 DOM, 如此以来, 未来对界面进行大量重构的时候 Javascript 仍然可以使用。

```jsx
<div class="ButtonAdd" id="button-add" js="ButtonAdd">
document.querySelector('.ButtonAdd') // NO
document.querySelector('#button-add') // YES
document.querySelectorAll('js[ButtonAdd]') // YES
```

## 图标

- 必须是 svg 格式
- 必须有 `<svg class="icon" width=".." height=".." viewBox="..">`
- 自定义颜色： `<path fill="currentColor">`

### 创建图标

```jsx
// src/icons/flag/hello.svg

<svg class="icon" width="24" height="24" viewBox="0 0 24 24">
  <path stroke="currentColor"></path>
</svg>
```

### 使用图标

```php
// hello.php

<?= icon('flag/hello') ?>
```
