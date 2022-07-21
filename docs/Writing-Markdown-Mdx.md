# Writing Markdown/MDX

## Text

```
**bold**
_italic_
**_bold+italic_**
~~strokethrough~~
<Important>text</Important>
<H1 id>Heading 1</H1>            // h1 with anchor
```

## Common

```
[Link](href)
https://autolink.com
<Button href>Button</Button>
{props.SITE_NAME}          // variable
```

## Table

```
| Header1 | Header2 | Header3 |
| :------ | :-----: | ------: |   // align left, center, right
| Left    | Center  | Right   |
| ^       | >       | Cell    |   // rowspan, colspan

<TableData
  columns = [
    { header: 'Header1', accessor: 'key1 }
  ]
  data = [
    { key1: 'value1' }
  ]
  align = 'left'
>
```
