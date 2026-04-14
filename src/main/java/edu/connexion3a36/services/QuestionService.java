package edu.connexion3a36.services;

import edu.connexion3a36.entities.Question;
import edu.connexion3a36.tools.MyConnection;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

public class QuestionService {

    private final Connection cnx;

    public QuestionService() {
        this.cnx = MyConnection.getInstance().getCnx();
    }

    public void ajouterQuestion(Question question) throws SQLException {
        String requete = "INSERT INTO question (question, correct_answer, score, type) VALUES (?, ?, ?, ?)";
        try (PreparedStatement pst = cnx.prepareStatement(requete)) {
            pst.setString(1, question.getQuestion());
            pst.setString(2, question.getCorrectAnswer());
            pst.setDouble(3, question.getScore());
            pst.setString(4, question.getType());
            pst.executeUpdate();
        }
    }

    public List<Question> afficherQuestion() throws SQLException {
        String requete = "SELECT id_question, question, correct_answer, score, type FROM question";
        List<Question> questions = new ArrayList<>();

        try (PreparedStatement pst = cnx.prepareStatement(requete);
             ResultSet rs = pst.executeQuery()) {
            while (rs.next()) {
                Question question = new Question();
                question.setIdQuestion(rs.getInt("id_question"));
                question.setQuestion(rs.getString("question"));
                question.setCorrectAnswer(rs.getString("correct_answer"));
                question.setScore(rs.getDouble("score"));
                question.setType(rs.getString("type"));
                questions.add(question);
            }
        }

        return questions;
    }

    public void modifierQuestion(Question question) throws SQLException {
        String requete = "UPDATE question SET question = ?, correct_answer = ?, score = ?, type = ? WHERE id_question = ?";
        try (PreparedStatement pst = cnx.prepareStatement(requete)) {
            pst.setString(1, question.getQuestion());
            pst.setString(2, question.getCorrectAnswer());
            pst.setDouble(3, question.getScore());
            pst.setString(4, question.getType());
            pst.setInt(5, question.getIdQuestion());
            pst.executeUpdate();
        }
    }

    public void supprimerQuestion(int id) throws SQLException {
        String requete = "DELETE FROM question WHERE id_question = ?";
        try (PreparedStatement pst = cnx.prepareStatement(requete)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }
}
