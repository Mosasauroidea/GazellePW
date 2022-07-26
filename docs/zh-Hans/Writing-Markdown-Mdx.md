# 书写 Markdown/MDX

## 文字

```
**bold**
_italic_
**_bold+italic_**
~~strokethrough~~
<Important>text</Important>
<H1 id>Heading 1</H1>            // h1 with anchor
```

## 常用

```
[Link](href)
https://autolink.com
<Button href>Button</Button>
{props.SITE_NAME}          // variable
```

## 表格

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
