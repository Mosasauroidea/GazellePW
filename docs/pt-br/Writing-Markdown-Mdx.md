# Escrevendo Markdown/MDX

## Texto

```
**bold**
_italic_
**_bold+italic_**
~~strokethrough~~
<Important>text</Important>
<H1 id>Heading 1</H1>            // h1 com âncora
```

## Uso Comum

```
[Link](href)
https://autolink.com
<Button href>Button</Button>
{props.SITE_NAME}          // variável
```

## Tabela

```
|  Header1  |  Header2  |  Header3  |
|  :------  |  :-----:  |  ------:  |   // alinhar esquerda, centro, direita
| Esquerda  |  Centro   |  Direita  |
| ^         |  >        |  Célula   |   // rowspan, colspan

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
