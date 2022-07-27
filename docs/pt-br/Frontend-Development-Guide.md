# Guia de Desenvolvimento Frontend

## Nomeação de CSS

> Olhe [Convenções de nomenclatura do SUIT CSS](https://github.com/suitcss/suit/blob/master/doc/naming-conventions.md)

### Nomeação de componentes

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

// Compor múltiplos componentes: class="Button ButtonSimple Box"
```

### Nomeação de variáveis

```css
/* Syntax: --ComponentName[-descendant|--modifier][-onState]-(cssProperty|variableName) */

--Button-color
--Button-backgroundColor
--Button-onHover-color
--Button-deepVeryLongDescentName-color
--Button-deepVeryLongDescentName-onHover-color
```

## Javascript

### Não acesse o DOM via classe

Em vez disso, utilize o `id` ou o atributo do `js` para acessar o DOM. Para que o refatoração da IU não quebre as características do Javascript.

```jsx
<div class="ButtonAdd" id="button-add" js="ButtonAdd">
document.querySelector('.ButtonAdd') // ❌
document.querySelector('#button-add') // ✔️
document.querySelectorAll('js[ButtonAdd]') // ✔️
```

## Ícone

- Deve estar em formato svg.
- Deve ter `<svg class="icon" width=".." height=".." viewBox="..">`
- Cor personalizada: `<path fill="currentColor">`

### Criar ícone

```jsx
// src/icons/flag/hello.svg

<svg class="icon" width="24" height="24" viewBox="0 0 24 24">
  <path stroke="currentColor"></path>
</svg>
```

### Usar ícone

```php
// hello.php

<?= icon('flag/hello') ?>
```
