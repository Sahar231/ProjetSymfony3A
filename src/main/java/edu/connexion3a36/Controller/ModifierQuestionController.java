package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Question;
import edu.connexion3a36.services.QuestionService;
import javafx.fxml.FXML;
import javafx.scene.control.ChoiceBox;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;

import java.sql.SQLException;

public class ModifierQuestionController {

    @FXML
    private TextArea questionTA;

    @FXML
    private TextField correctAnswerTF;

    @FXML
    private TextField scoreTF;

    @FXML
    private ChoiceBox<String> typeChoice;

    private final QuestionService questionService = new QuestionService();
    private Question selectedQuestion;

    @FXML
    public void initialize() {
        typeChoice.getItems().addAll("Choix Multiple", "Vrai/Faux", "Directe");
    }

    public void setQuestion(Question question) {
        this.selectedQuestion = question;
        if (question != null) {
            questionTA.setText(question.getQuestion());
            correctAnswerTF.setText(question.getCorrectAnswer());
            scoreTF.setText(String.valueOf(question.getScore()));
            typeChoice.setValue(question.getType());
        }
    }

    @FXML
    public void modifierQuestion() {
        if (selectedQuestion == null) { ControllerUtils.showWarning("Selectionnez une question a modifier."); return; }
        if (!ControllerUtils.isTextValid(questionTA) || !ControllerUtils.isTextValid(correctAnswerTF)) { ControllerUtils.showError("Le texte et la réponse correcte doivent être renseignés."); return; }
        if (!ControllerUtils.isDouble(scoreTF)) { ControllerUtils.showError("Le score doit être un nombre valide."); return; }
        double score = Double.parseDouble(scoreTF.getText().trim());
        String type = typeChoice.getValue();
        selectedQuestion.setQuestion(questionTA.getText().trim());
        selectedQuestion.setCorrectAnswer(correctAnswerTF.getText().trim());
        selectedQuestion.setScore(score);
        selectedQuestion.setType(type);
        try {
            questionService.modifierQuestion(selectedQuestion);
            ControllerUtils.showInfo("Question modifiée avec succès.");
            // go back to list after modification (preserve CSS)
            ControllerUtils.navigateTo(questionTA, "/question_list.fxml");
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de la modification de la question : " + e.getMessage());
        }
    }

    @FXML
    private void handleCancel(javafx.event.ActionEvent event) {
        // Invoked by the Cancel button in question_edit.fxml
        handleBack(event);
    }

    @FXML
    private void handleBack(javafx.event.ActionEvent event) {
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(getClass().getResource("/home.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Node source = (javafx.scene.Node) event.getSource();
            javafx.stage.Stage stage = (javafx.stage.Stage) source.getScene().getWindow();
            javafx.scene.Scene scene = stage.getScene();
            if (scene == null) {
                javafx.scene.Scene newScene = new javafx.scene.Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (java.io.IOException e) {
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }
}
