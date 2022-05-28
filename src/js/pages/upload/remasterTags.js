import { without, compact } from 'lodash-es'

function remasterTags(a, label) {
  const hideVal = $('#remaster_title_hide').val()
  const showVal = $('#remaster_title_show').val()
  if (!hasTag(hideVal, label)) {
    $(a).css('color', '#ffbb33')
    $('#remaster_title_hide').val(addTag(hideVal, label))
    $('#remaster_title_show').val(addTag(showVal, $(a).text()))
  } else {
    $(a).css('color', '#337ab7')
    $('#remaster_title_hide').val(removeTag(hideVal, label))
    $('#remaster_title_show').val(removeTag(showVal, $(a).text()))
  }
  $('.FormValidation')[0].validator.validate()
}

function hasTag(value, tag) {
  return value.split(' / ').includes(tag)
}

function removeTag(value, tag) {
  return without(value.split(' / '), tag).join(' / ')
}

function addTag(value, tag) {
  return [...compact(value.split(' / ')), tag].join(' / ')
}

window.remasterTags = remasterTags
